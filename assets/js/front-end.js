jQuery(function($) {
    "use strict";

    // Utility function to delay execution
    const delay = (function() {
        let timer = 0;
        return function(callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    let autoCompleteAddress = 1;

    // NL Helper object for handling form functionalities specific to the Netherlands
    const nlHelper = {
        inputFields: ["address_1_field", "address_2_field", "city_field", "postcode_field"],

        init() {
            this.bindCountryChangeEvents();
            this.handleInitialCountryChange('billing');
            this.handleInitialCountryChange('shipping');
        },

        bindCountryChangeEvents() {
            $("#billing_country").on('change', () => this.handleCountryChange('billing')).trigger('change');
            $("#shipping_country").on('change', () => this.handleCountryChange('shipping')).trigger('change');
        },

        handleInitialCountryChange(fieldTypes) {
            this.handleCountryChange(fieldTypes);
        },

        handleCountryChange(fieldTypes) {
            const isNL = $(`#${fieldTypes}_country`).val() === 'NL';
            const showAction = nlcac_data.address_fields === "always_show" ? 'show' : 'hide';

            if (isNL) {
                if (nlcac_data.choose_autocomplete_type === 'address') {
                    this.autoCompleteAddress(fieldTypes);
                    this.autoCompleteType(fieldTypes);
                } else {
                    this.toggleInputFields(fieldTypes, showAction);
                    $(`.nl-${fieldTypes}`).show();
                    $(`#nl_${fieldTypes}_enable_disable_field`).show();
                    this.handlePostcodeForm(fieldTypes);

                    if (nlcac_data.editable_fields === "no") {
                        this.toggleEditableFields(fieldTypes, false);
                    }
                }
            } else {
                $(`.nl-${fieldTypes}`).hide();
                $(`#nl_${fieldTypes}_enable_disable_field`).hide();
                this.toggleEditableFields(fieldTypes, true);
                $(`#${fieldTypes}_postcode_field, #${fieldTypes}_address_2_field`).removeClass("nl-hide-unnecessary-input");
            }
        },

        toggleInputFields(fieldTypes, action) {
            this.inputFields.forEach(element => {
                const $element = $(`#${fieldTypes}_${element}`);
                if (action === 'show') {
                    $element.fadeIn("slow").removeClass("nl-add-hide").addClass("nl-add-show");
                } else if (action === 'hide') {
                    $element.fadeOut("slow").addClass("nl-add-hide").removeClass("nl-add-show");
                }
                $(`.nl-${fieldTypes}`).show();
            });
        },

        toggleEditableFields(fieldTypes, isEditable) {
            this.inputFields.forEach(element => {
                $(`#${fieldTypes}_${element.replace('_field', '')}`).prop('readonly', !isEditable);
            });
        },

        updateInputAnimation(fieldTypes, action) {
            this.inputFields.forEach(element => {
                const $element = $(`#${fieldTypes}_${element}`);
                if (action === 'show') {
                    $element.addClass("nl-input-animation");
                } else if (action === 'hide') {
                    $element.removeClass("nl-input-animation");
                }
            });
        },

        handlePostcodeForm(fieldTypes) {
            const $postcodeField = $(`#${fieldTypes}_postcode_field`).addClass("nl-hide-unnecessary-input");
            const $address2Field = $(`#${fieldTypes}_address_2_field`).addClass("nl-hide-unnecessary-input");
            const url = `${nlcac_data.api}postcode-information/v1/info`;

            $(`#nl_${fieldTypes}_find_address_postcode, #nl_${fieldTypes}_find_house_no, #nl_${fieldTypes}_find_toev`).on('keyup', () => {
                $(".nl-message").remove();

                const postcode = $(`#nl_${fieldTypes}_find_address_postcode`).val();
                const houseNumber = $(`#nl_${fieldTypes}_find_house_no`).val();
                const addition = $(`#nl_${fieldTypes}_find_toev`).val();

                if (postcode.length >= 6 && houseNumber) {
                    delay(() => {
                        this.updateInputAnimation(fieldTypes, 'show');

                        $.ajax({
                            url: url,
                            type: 'GET',
                            data: {
                                postcode: postcode,
                                house_number: houseNumber,
                                addition: addition,
                                website: nlcac_data.website,
                            },
                            success: response => {
                                $(".nl-message").remove();

                                if (response.success) {
                                    const address = `${response.data.street} ${response.data.house_number} ${addition}`;
                                    $(`#${fieldTypes}_address_1`).val(address);
                                    $(`#${fieldTypes}_postcode`).val(response.data.postcode);
                                    $(`#${fieldTypes}_city`).val(response.data.city);
                                    $(`#${fieldTypes}_city`).val( "Choose Another Address" );

                                    this.toggleInputFields(fieldTypes, nlcac_data.address_fields === "hide" ? 'hide' : 'show');
                                } else {
                                    $(`#${fieldTypes}_address_1, #${fieldTypes}_postcode, #${fieldTypes}_city`).val('');
                                    $(`#nl_${fieldTypes}_find_toev_field`).after(`<p class='nl-message'>${response.message}</p>`);
                                }
                                this.updateInputAnimation(fieldTypes, 'hide');
                            },
                            error: (xhr, status, error) => console.error("Error:", error)
                        });
                    }, 1200);
                }
            });

            $(`#nl_${fieldTypes}_enable_disable`).on('change', function() {
                const isChecked = this.checked;
                nlHelper.toggleInputFields(fieldTypes, isChecked ? 'show' : 'hide');
                $(`.nl-${fieldTypes}`).toggle(!isChecked);
                nlHelper.toggleEditableFields(fieldTypes, isChecked);
                $(`#${fieldTypes}_postcode_field, #${fieldTypes}_address_2_field`).toggleClass("nl-hide-unnecessary-input", !isChecked);
            });
        },

        autoCompleteAddress(fieldTypes) {
            const $manualAddressField = $(`#nl_${fieldTypes}_find_address_field`);
            const $manualAddressLink = $(`<div class="nl-address-list" id="nl-${fieldTypes}-address-list"></div><span class="nl-descriptions"><a href="#" id="nl-manually-${fieldTypes}-address" class="nl-manually-address">Enter address manually</a></span>`);

            if (!$manualAddressField.find(`#nl-manually-${fieldTypes}-address`).length) {
                $manualAddressField.append($manualAddressLink);
            }
            $manualAddressField.fadeIn("slow");
            this.toggleInputFields(fieldTypes, 'hide');

            $manualAddressLink.on("click", function(event) {
                event.preventDefault();
                const $wrapper = $manualAddressField.find(".woocommerce-input-wrapper");
                const $fndAddress = $manualAddressField.find(".fndadress");

                if (autoCompleteAddress === 1) {
                    $wrapper.fadeOut("hide");
                    $fndAddress.fadeOut("hide");
                    $(this).find('a').text("Address Finder");
                    autoCompleteAddress = 2;
                    nlHelper.toggleInputFields(fieldTypes, 'show');
                } else {
                    $wrapper.fadeIn("slow");
                    $fndAddress.fadeIn("slow");
                    $(this).find('a').text("Enter address manually");
                    autoCompleteAddress = 1;
                    nlHelper.toggleInputFields(fieldTypes, 'hide');
                }
            });

        },
        autoCompleteType(fieldTypes) {
            $(`#nl_${fieldTypes}_find_address`).on( "keyup", function () {
                var keyword = $( this ).val();
                console.log( fieldTypes );
                var address_template = $('#dynamic-address-list-template').html();
                var logo_template = $('#nl-logo-template').html();

                $(window).on("click", function () {
                    $(`#nl-${fieldTypes}-address-list`).hide();
                })

                const url = `${nlcac_data.api}postcode-information/v1/auto`;
                $(`#nl_${fieldTypes}_find_address_field`).addClass("nl-input-animation");

                delay(() => {

                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            keyword: keyword,
                            website: nlcac_data.website,
                            page: 1,
                            per_page: 15
                        },
                        success: function(response) {
                            if (response.success) {
                                var allItemsHtml = '<ul class="nl-address-list-su">';

                                response.data.forEach(function (single_address) {
                                    var full_address = single_address.street + " "+ single_address.house_number  + " "+ single_address.toevoeging  + " "+ single_address.postcode  + " " + single_address.city;
                                    var full_address = full_address.replace("  ", " ");
                                    var bookHtml = address_template.replace('{{address}}', full_address );
                                    var bookHtml = bookHtml.replace('{{toevoeging}}', single_address.toevoeging );
                                    var bookHtml = bookHtml.replace('{{postcode}}', single_address.postcode );
                                    var bookHtml = bookHtml.replace('{{city}}', single_address.city );
                                    var bookHtml = bookHtml.replace('{{house_number}}', single_address.house_number );
                                    var bookHtml = bookHtml.replace('{{street}}', single_address.street );
                                    allItemsHtml += bookHtml;
                                });

                                allItemsHtml += '</ul>';
                                allItemsHtml += logo_template;

                                $(`#nl-${fieldTypes}-address-list`).html( allItemsHtml );
                                $(`#nl-${fieldTypes}-address-list`).show();

                                $(".nl-address-single").on( "click", function ( event ) {
                                    event.preventDefault();
                                    $(`#nl-${fieldTypes}-address-list`).hide();
                                    var street = $( this ).data( "street" );
                                    var house_number = $( this ).data( "house_number" );
                                    var postcode = $( this ).data( "postcode" );
                                    var city = $( this ).data( "city" );
                                    var toevoeging = $( this ).data( "toevoeging" );

                                    const address = `${street} ${house_number} ${toevoeging}`;
                                    $(`#${fieldTypes}_address_1`).val(address);
                                    $(`#${fieldTypes}_postcode`).val(postcode);
                                    $(`#${fieldTypes}_city`).val(city);
                                    $(`#nl-manually-${fieldTypes}-address`).text("Choose Another Address");
                                });
                            } else {
                                console.log('No data found');
                            }
                            $(`#nl_${fieldTypes}_find_address_field`).removeClass("nl-input-animation");
                        },
                        error: function(xhr, status, error) {
                            console.log('Error: ' + error);
                        }
                    });





                }, 1200);

            });
        }
    };

    // Initialization
    nlHelper.init();

    // Additional generic functionalities
    $('.nl-field label span').remove();
    $('.nl-required label').append('<abbr class="required" title="verplicht">*</abbr>');


});


