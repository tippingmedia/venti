Venti.ElementEditor = Garnish.Base.extend({
    $element: null,
    elementId: null,
    siteId: null,

    $form: null,
    $fieldsContainer: null,
    $cancelBtn: null,
    $saveBtn: null,
    $spinner: null,

    $siteSelect: null,
    $siteSpinner: null,

    modal: null,

    init: function($element, settings) {
        // Param mapping
        if (typeof settings == typeof undefined && $.isPlainObject($element)) {
            // (settings)
            settings = $element;
            $element = null;
        }

        this.$element = $element;
        this.setSettings(settings, Venti.ElementEditor.defaults);

        this.loadModal();
    },

    setElementAttribute: function(name, value) {
        if (!this.settings.attributes) {
            this.settings.attributes = {};
        }

        if (value === null) {
            delete this.settings.attributes[name];
        } else {
            this.settings.attributes[name] = value;
        }
    },

    getBaseData: function() {
        var data = $.extend({}, this.settings.params);

        if (this.settings.siteId) {
            data.siteId = this.settings.siteId;
        } else if (this.$element && this.$element.data('site-id')) {
            data.siteId = this.$element.data('site-id');
        }

        if (this.settings.elementId) {
            data.elementId = this.settings.elementId;
        } else if (this.$element && this.$element.data('id')) {
            data.elementId = this.$element.data('id');
        }

        if (this.settings.elementType) {
            data.elementType = this.settings.elementType;
        }

        if (this.settings.attributes) {
            data.attributes = this.settings.attributes;
        }

        return data;
    },

    loadModal: function() {
        this.onBeginLoading();
        var data = this.getBaseData();
        //Reset locale to store locale id so modal will show correct event version to edit.
        data.siteId = Craft.siteId;
        data.includeSites = this.settings.showSiteSwitcher;
        Craft.postActionRequest('elements/get-editor-html', data, $.proxy(this, 'showModal'));
    },

    showModal: function(response, textStatus) {
        this.onEndLoading();

        if (textStatus == 'success') {
            var $modal = $('<form class="modal venti-elementeditor-modal"></form>').appendTo(Garnish.$bod),
                $header = $('<div class="header"></div>'),
                $contents = $();



            if (response.sites) {
                var $colLeft = $('<div class="col"/>').appendTo($header),
                    $siteSelectContainer = $('<div class="select"/>').appendTo($colLeft);

                this.$siteSelect = $('<select/>').appendTo($siteSelectContainer);
                this.$siteSpinner = $('<div class="spinner hidden"/>').appendTo($colLeft);

                for (var i = 0; i < response.sites.length; i++) {
                    var site = response.sites[i];
                    $('<option value="' + site.id + '"' + (site.id == response.site ? ' selected="selected"' : '') + '>' + site.name + '</option>').appendTo(this.$siteSelect);
                }

                this.addListener(this.$siteSelect, 'change', 'switchSite');

            }

            $header.appendTo($modal);

            this.$form = $('<div class="body elementeditor "/>');
            this.$fieldsContainer = $('<div class="fields"/>').appendTo(this.$form);

            this.updateForm(response);
            this.onCreateForm(this.$form);

            var $footer = $('<div class="footer"></div>'),
                $buttonsContainer = response.sites ? $('<div class="col"/>').appendTo($header) : $('<div class="text--right"/>').appendTo($header);
            this.$cancelBtn = $('<div class="btn">' + Craft.t('venti', 'Cancel') + '</div>').appendTo($buttonsContainer);
            this.$saveBtn = $('<input class="btn submit" type="submit" value="' + Craft.t('venti', 'Save') + '"/>').appendTo($buttonsContainer);
            this.$spinner = $('<div class="spinner hidden"/>').appendTo($buttonsContainer);

            $contents = $contents.add(this.$form);
            //$contents = $contents.add($footer);

            $contents.appendTo($modal);

            if (!this.modal) {

                this.modal = new Garnish.Modal($modal, {
                    closeOtherModals: true,
                    visible: true,
                    resizable: true,
                    shadeClass: 'modal-shade dark',
                    onShow: $.proxy(this, 'onShowModal'),
                    onHide: $.proxy(this, 'onHideModal')
                });

                this.modal.$container.data('elementEditor', this);

                this.modal.on('hide', $.proxy(function() {
                    delete this.modal;
                }, this));
            } else {
                //this.modal.updateBody($modalContents);
                this.modal.updateSizeAndPosition();
            }

            // Focus on the first text input
            $modal.find('.text:first').focus();

            this.addListener(this.$cancelBtn, 'click', function() {
                this.modal.hide()
            });

            this.addListener(this.$saveBtn, 'click', this.saveElement);
        }
    },

    switchSite: function() {
        var newSite = this.$siteSelect.val();

        if (newSite == this.siteId) {
            return;
        }

        this.$siteSpinner.removeClass('hidden');


        var data = this.getBaseData();
        data.siteId = newSite;

        Craft.postActionRequest('elements/get-editor-html', data, $.proxy(function(response, textStatus) {
            this.$siteSpinner.addClass('hidden');

            if (textStatus == 'success') {
                this.updateForm(response);
            } else {
                this.$siteSelect.val(this.siteId);
            }
        }, this));
    },

    updateForm: function(response) {
        this.siteId = response.siteId;

        this.$fieldsContainer.html(response.html);

        // Swap any instruction text with info icons
        var $instructions = this.$fieldsContainer.find('> .meta > .field > .heading > .instructions');

        for (var i = 0; i < $instructions.length; i++) {

            $instructions.eq(i)
                .replaceWith($('<span/>', {
                    'class': 'info',
                    'html': $instructions.eq(i).children().html()
                }))
                .infoicon();
        }

        Garnish.requestAnimationFrame($.proxy(function() {
            Craft.appendHeadHtml(response.headHtml);
            Craft.appendFootHtml(response.footHtml);
            Craft.initUiElements(this.$fieldsContainer);
        }, this));
    },

    saveElement: function(evt) {
        evt.preventDefault();
        var validators = this.settings.validators;

        if ($.isArray(validators)) {
            for (var i = 0; i < validators.length; i++) {
                if ($.isFunction(validators[i]) && !validators[i].call()) {
                    return false;
                }
            }
        }

        this.$spinner.removeClass('hidden');

        var data = $.param(this.getBaseData()) + '&' + this.modal.$container.serialize();
        Craft.postActionRequest('elements/save-element', data, $.proxy(function(response, textStatus) {
            this.$spinner.addClass('hidden');

            if (textStatus == 'success') {
                if (textStatus == 'success' && response.success) {
                    if (this.$element && this.siteId == this.$element.data('siteId')) {
                        // Update the label
                        var $title = this.$element.find('.title'),
                            $a = $title.find('a');

                        if ($a.length && response.cpEditUrl) {
                            $a.attr('href', response.cpEditUrl);
                            $a.text(response.newTitle);
                        } else {
                            $title.text(response.newTitle);
                        }
                    }

                    this.closeModal();
                    this.onSaveElement(response);
                } else {
                    this.updateForm(response);
                    Garnish.shake(this.modal.$modal);
                }
            }
        }, this));
    },

    closeModal: function() {
        this.modal.hide();
        delete this.modal;
    },

    // Events
    // -------------------------------------------------------------------------

    onShowModal: function() {
        this.settings.onShowModal();
        this.trigger('showModal');
    },

    onHideModal: function() {
        this.settings.onHideModal();
        this.trigger('hideModal');
    },

    onBeginLoading: function() {
        if (this.$element) {
            this.$element.addClass('loading');
        }

        this.settings.onBeginLoading();
        this.trigger('beginLoading');
    },

    onEndLoading: function() {
        if (this.$element) {
            this.$element.removeClass('loading');
        }

        this.settings.onEndLoading();
        this.trigger('endLoading');
    },

    onSaveElement: function(response) {
        this.settings.onSaveElement(response);
        this.trigger('saveElement', {
            response: response
        });
    },

    onCreateForm: function($form) {
        this.settings.onCreateForm($form);
    }
}, {
    defaults: {
        showSiteSwitcher: true,
        elementId: null,
        elementType: null,
        siteId: null,
        attributes: null,
        params: null,
        onShowModal: $.noop,
        onHideModal: $.noop,
        onBeginLoading: $.noop,
        onEndLoading: $.noop,
        onCreateForm: $.noop,
        onSaveElement: $.noop,

        validators: []
    }
});