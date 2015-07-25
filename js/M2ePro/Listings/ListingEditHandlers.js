ListingEditHandlers = Class.create();
ListingEditHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {
        
        this.setValidationCheckRepetitionValue('M2ePro-listing-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Listings', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-input-datetime', M2ePro.text.wrong_date_time_format_error, function(value) {
            var found = value.match(/^\d{4}-\d{2}-\d{1,2}\s\d{2}:\d{2}:\d{2}$/g);
            return found ? true : false;
        });
    },

    //----------------------------------

    attribute_set_change: function(event)
    {
        ListingEditHandlersObj.hideEmptyOption(this);

        ListingEditHandlersObj.reloadSellingFormatTemplates();
        ListingEditHandlersObj.reloadListingTemplates();
        ListingEditHandlersObj.reloadDescriptionTemplates();

        $$('button.add').each(function(obj) {
            var onclickAction = obj.readAttribute('onclick');

            if (onclickAction.match(/attribute_set_id\/[-]?\d+/)) {
                onclickAction = onclickAction.replace(/attribute_set_id\/[-]?\d+/, 'attribute_set_id/' + $('attribute_set_id').value + '/');
            } else {
                onclickAction = onclickAction.replace(/new\//, 'new/attribute_set_id/' + $('attribute_set_id').value + '/');
            }

            obj.writeAttribute('onclick', onclickAction);
        });
    },

    //----------------------------------

    reloadSellingFormatTemplates: function()
    {
        var attribute_set_id = $('attribute_set_id').value;
        if (typeof attribute_set_id == 'undefined' || attribute_set_id == null || attribute_set_id == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getSellingFormatTemplatesBySet + 'attribute_set_id/' + attribute_set_id, 'selling_format_template_id');
    },

    reloadListingTemplates: function()
    {
        var attribute_set_id = $('attribute_set_id').value;
        if (typeof attribute_set_id == 'undefined' || attribute_set_id == null || attribute_set_id == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getListingTemplatesBySet + 'attribute_set_id/' + attribute_set_id, 'listing_template_id');
    },

    reloadDescriptionTemplates: function()
    {
        var attribute_set_id = $('attribute_set_id').value;
        if (typeof attribute_set_id == 'undefined' || attribute_set_id == null || attribute_set_id == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getDescriptionTemplatesBySet + 'attribute_set_id/' + attribute_set_id, 'description_template_id');
    },

    reloadSynchronizationTemplates: function()
    {
        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getSynchronizationTemplates, 'synchronization_template_id');
    },

    //----------------------------------

    synchronization_template_id_change: function()
    {
        ListingEditHandlersObj.hideEmptyOption(this);
    },

    synchronization_start_type_change: function()
    {
        var value = $('synchronization_start_type').value;

        if (value == 1) {
            $('synchronization_start_through_value_container').style.display = 'none';
            $('synchronization_start_date_container').style.display = 'none';
        } else if (value == 2) {
            $('synchronization_start_through_value_container').style.display = '';
            $('synchronization_start_date_container').style.display = 'none';
        } else if (value == 3) {
            $('synchronization_start_through_value_container').style.display = 'none';
            $('synchronization_start_date_container').style.display = '';
        } else{
            $('synchronization_start_through_value_container').style.display = 'none';
            $('synchronization_start_date_container').style.display = 'none';
        }
    },

    synchronization_stop_type_change: function()
    {
        var value = $('synchronization_stop_type').value;

        if (value == 0) {
            $('synchronization_stop_through_value_container').style.display = 'none';
            $('synchronization_stop_date_container').style.display = 'none';
        } else if (value == 1) {
            $('synchronization_stop_through_value_container').style.display = '';
            $('synchronization_stop_date_container').style.display = 'none';
        } else if (value == 2) {
            $('synchronization_stop_through_value_container').style.display = 'none';
            $('synchronization_stop_date_container').style.display = '';
        } else {
            $('synchronization_stop_through_value_container').style.display = 'none';
            $('synchronization_stop_date_container').style.display = 'none';
        }
    },

    //----------------------------------

    reloadByAttributeSet: function(url, id)
    {
        new Ajax.Request(url, {
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);

                var options = '';

                var firstItemValue = '';
                var currentValue = $(id).value;

                data.each(function(paris) {
                    var key = (typeof paris.key != 'undefined') ? paris.key : paris.id;
                    var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
                    options += '<option value="' + key + '">' + val + '</option>\n';

                    if (firstItemValue == '') {
                        firstItemValue = val;
                    }
                });

                $(id).update();
                $(id).insert(options);

                if (currentValue != '') {
                    $(id).value = currentValue;
                } else {
                    if (M2ePro.formData[id] > 0) {
                        $(id).value = M2ePro.formData[id];
                    } else {
                        $(id).value = firstItemValue;
                    }
                }
            }
        });
    }
    
    //----------------------------------
});