<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Feedbacks_Response extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 50;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 50;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Response Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Response" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Response" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Get no responsed feedbacks
        //-----------------------
        $feedbacks = Mage::getModel('M2ePro/Feedbacks')->getLastNoResponsed(60);

        $tempFeedbacks = array();
        foreach ($feedbacks as $feedback) {
            $account = Mage::getModel('M2ePro/Accounts')->loadInstance($feedback->getData('account_id'));
            if (!$account->isFeedbacksReceive()) {
                continue;
            }
            if ($account->isFeedbacksAutoResponseDisabled()) {
                continue;
            }
            if ($account->isFeedbacksAutoResponseOnlyPositive() && !$feedback->isPositive()) {
                continue;
            }
            if (!$account->hasFeedbacksTemplates()) {
                continue;
            }
            $tempFeedbacks[] = $feedback;
        }
        $feedbacks = $tempFeedbacks;

        if (count($feedbacks) == 0) {
            return;
        }
        //-----------------------

        // Process feedbacks
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / count($feedbacks);

        foreach ($feedbacks as $feedback) {

            // Get account model
            $account = Mage::getModel('M2ePro/Accounts')->loadInstance($feedback->getData('account_id'));

            // Get response body
            //-----------------------
            $body = $this->getResponseBody($account);
            if ($body == '') {
                 continue;
            }
            //-----------------------

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            // Create connector
            //-----------------------
            $feedback->sendResponse($body,Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE);

            $paramsConnector = array(
                'transaction_id' => $feedback->getData('ebay_transaction_id'),
                'item_id'        => $feedback->getData('ebay_item_id')
            );
            Mage::getModel('M2ePro/Feedbacks')->receiveFeedbacks($feedback->getAccount(), $paramsConnector);
            //-----------------------

            $this->_profiler->addTitle('Send feedback for "'.$feedback->getData('buyer_name').'"');
            $this->_profiler->addTitle('His feedback "'.$feedback->getData('buyer_feedback_text').'" ('.$feedback->getData('buyer_feedback_type').')');
            $this->_profiler->addTitle('Our feedback "'.$body.'"');

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;
        }
        //-----------------------
    }

    //####################################

    private function getResponseBody($account)
    {
        if ($account->isFeedbacksAutoResponseCycled()) {

            $lastUsedId = 0;
            if ($account->getFeedbacksLastUsedId() != null) {
                $lastUsedId = (int)$account->getFeedbacksLastUsedId();
            }

            $tempArray = Mage::getModel('M2ePro/FeedbacksTemplates')
                                    ->getCollection()
                                    ->addFieldToFilter('account_id', $account->getId())
                                    ->addFieldToFilter('id', array('gt'=>$lastUsedId))
                                    ->setOrder('id','ASC')
                                    ->toArray();

            $nextUsedId = 0;
            if (count($tempArray['items']) == 0) {

                $tempArray = Mage::getModel('M2ePro/FeedbacksTemplates')
                                    ->getCollection()
                                    ->addFieldToFilter('account_id', $account->getId())
                                    ->setOrder('id','ASC')
                                    ->toArray();

                if (count($tempArray['items']) == 0) {
                    return '';
                } else {
                    $nextUsedId = (int)$tempArray['items'][0]['id'];
                }

            } else {
                $nextUsedId = (int)$tempArray['items'][0]['id'];
            }

            $account->addData(array('feedbacks_last_used_id'=>$nextUsedId))->save();

            $feedbackTemplate = Mage::getModel('M2ePro/FeedbacksTemplates')->loadInstance($nextUsedId);
            return $feedbackTemplate->getBody();
        }

        if ($account->isFeedbacksAutoResponseRandom()) {

            $size = Mage::getModel('M2ePro/FeedbacksTemplates')
                                    ->getCollection()
                                    ->addFieldToFilter('account_id', $account->getId())
                                    ->getSize();

            $index = rand(0,$size-1);

            $tempArray = Mage::getModel('M2ePro/FeedbacksTemplates')
                                    ->getCollection()
                                    ->addFieldToFilter('account_id', $account->getId())
                                    ->toArray();

            $feedbackTemplate = Mage::getModel('M2ePro/FeedbacksTemplates')->loadInstance($tempArray['items'][$index]['id']);
            return $feedbackTemplate->getBody();
        }

        return '';
    }

    //####################################
}