SynchronizationTemplatesHandlers = Class.create();
SynchronizationTemplatesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'SynchronizationsTemplates', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-input-time', M2ePro.text.wrong_time_format_error, function(value) {
            var found = value.match(/^\d{2}:\d{2}$/g);
            return found ? true : false;
        });
    },

    //----------------------------------

    stopQty_change : function()
    {
        if ($('stop_qty').value == 0) {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == 1) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == 2) {
            $('stop_qty_item_min').show();
            $('stop_qty_item').hide();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').show();
        } else if ($('stop_qty').value == 3) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();   
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        }
    },

    relistMode_change : function()
    {
        var self = SynchronizationTemplatesHandlersObj;

        if ($('relist_mode').value == 0) {
            $('relist_filter_user_lock_tr_container').hide();
            $('magento_block_synchronization_template_relist_rules').hide();
            $('magento_block_synchronization_template_relist_schedule').hide();
        } else if ($('relist_mode').value == 1) {
            $('relist_filter_user_lock_tr_container').show();
            $('magento_block_synchronization_template_relist_rules').show();
            $('magento_block_synchronization_template_relist_schedule').show();
        } else {
            $('relist_filter_user_lock_tr_container').hide();
            $('magento_block_synchronization_template_relist_rules').hide();
            $('magento_block_synchronization_template_relist_schedule').hide();
        }

        $('relist_schedule_type').value = 0;
        self.relistScheduleType_change();
    },

    relistQty_change : function()
    {
        if ($('relist_qty').value == 0) {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == 1) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == 2) {
            $('relist_qty_item_min').show();
            $('relist_qty_item').hide();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').show();
        } else if ($('relist_qty').value == 3) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        }
    },

    relistScheduleType_change : function()
    {
        var self = SynchronizationTemplatesHandlersObj;

        if ($('relist_schedule_type').value == 0) {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else if ($('relist_schedule_type').value == 1) {
            $('relist_schedule_through_value_container').show();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else if ($('relist_schedule_type').value == 2) {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').show();
            $('relist_schedule_week_time_container').show();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        }
    },

    relistScheduleWeekTime_change : function()
    {
        if ($('relist_schedule_week_time').value == 0) {
            $('relist_schedule_week_start_time_container').hide();
            $('relist_schedule_week_end_time_container').hide();
        } else {
            $('relist_schedule_week_start_time_container').show();
            $('relist_schedule_week_end_time_container').show();
        }
    }

    //----------------------------------
});