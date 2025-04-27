import DocumentService from '@typo3/core/document-service.js';
import Notification from '@typo3/backend/notification.js';
import Modal from '@typo3/backend/modal.js';
import $ from 'jquery';
import Severity from '@typo3/backend/severity.js';

class SocialFeedAdministrationModule {
  constructor() {
    this._isRunning = false;
    this._domElementsSelectors = {
      confirmationButton: '.delete-action,.confirmation-action',
      selectSocialType: '#select-type',
      socialTypeUrlKeep: '#type-url-',
      winStorageBrowser: '[data-identifier="browse-feeds-storage"]',
      feedsStorageInput: '[data-identifier="feeds-storage-input"]',
      feedsStorageTitle: '[data-identifier="feed-storage-title"]',
      copyRedirectUriButton: '.copy-redirect-uri-button',
      facebookLoginButton: '.facebook-login-link',
      redirectUrl: '#redirect-uri-holder-2',
    };
    DocumentService.ready().then(() => {
      this.initialize();
    });
  }

  initialize() {
    if (this._isRunning === false) {
      this._bootstrap();
    }
    let _isRunning = true;
  }

  _bootstrap() {
    this._deleteConfirmation();
    this._facebookLoginWindow();
    this._changeSocialType();
    this._winStorageBrowser();
    this._getRedirectUriButtonClick();
    this._activateTabs();
  }

  /**
   * Activate the tabs for the backend module
   *
   * @private
   */
  _activateTabs() {
    const triggerTabList = [].slice.call(document.querySelectorAll('#tabs a'));
    const parent = document.querySelectorAll('#tabs')[0].parentElement;
    const contentContainer = parent.querySelector('.tab-content');

    triggerTabList.forEach(function (triggerEl) {
      const targetId = triggerEl.getAttribute('href').slice(1,);

      triggerEl.addEventListener('click', function (event) {
        event.preventDefault();

        for (let elem of contentContainer.children) {
          if (elem.id === targetId) {
            elem.classList.add('active');
            elem.classList.add('show');
          } else {
            elem.classList.remove('active');
            elem.classList.remove('show');
          }
        }
      });
    });
  }

  /**
   * If user try to delete something
   *
   * @private
   */
  _deleteConfirmation() {
    this._getDomElementByIdentifier('confirmationButton').addEventListener('click', function (e) {
      e.preventDefault();

      let $this = $(this);
      let title = $this.data('confirmation-title') || 'Delete';
      let message = $this.data('confirmation-message') || 'Are you sure you want to delete this record?';

      let url = $this.attr('href'),
        modal = Modal.confirm(title, message, Severity.warning);

      modal.addEventListener('confirm.button.cancel', function () {
        Modal.dismiss(modal);
      });

      modal.addEventListener('confirm.button.ok', function () {
        Modal.dismiss(modal);
        window.location.href = url;
      });
    });
  }

  /**
   * Show window with facebook login
   *
   * @private
   */
  _facebookLoginWindow() {
    this._getDomElementByIdentifier('facebookLoginButton').addEventListener('click', function (e) {
      e.preventDefault();

      let $this = $(this);
      const w = 800;
      const h = 800;

      const y = window.top.outerHeight / 2 + window.top.screenY - h / 2;
      const x = window.top.outerWidth / 2 + window.top.screenX - w / 2;

      window.open($this.attr('href'), 'Facebook login', 'height=' + h + ',width=' + w + 'top=' + y + ', left=' + x);
    });
  }

  /**
   * Switch to different social type
   *
   * @private
   */
  _changeSocialType() {
    const elem = this._getDomElementByIdentifier('selectSocialType');
    if (!elem) return;

    elem.addEventListener('change', function () {
      let selectSocialType = $(this).find(':selected').val();

      window.location.href = $(_getDomElementIdentifier('socialTypeUrlKeep') + selectSocialType).val();
    });
  }

  /**
   * Copy redirect uri to clipboard
   * @private
   */
  _getRedirectUriButtonClick() {
    const elem = this._getDomElementByIdentifier('copyRedirectUriButton');
    const clipboardText = this._getDomElementByIdentifier('redirectUrl');

    if (!elem || !clipboardText) return;
    elem.addEventListener('click', () => {
      navigator.clipboard.writeText(clipboardText.innerText);
    })
  }

  /**
   * Load browser pages window
   *
   * @private
   */
  _winStorageBrowser() {
    window.addEventListener('message', function (e) {
      if (!MessageUtility.MessageUtility.verifyOrigin(e.origin)) {
        throw 'Denied message sent by ' + e.origin;
      }

      if (typeof e.data.fieldName === 'undefined') {
        throw 'fieldName not defined in message';
      }

      if (typeof e.data.value === 'undefined') {
        throw 'value not defined in message';
      }

      const fieldElement = this._getInsertTarget(e.data.fieldName);
      if (fieldElement) {
        fieldElement.value = e.data.value;
      }

      const storageTitleElement = document.querySelector(this._getDomElementByIdentifier('feedsStorageTitle'));
      if (storageTitleElement) {
        storageTitleElement.innerHTML = e.data.label;
      }
    });

    const elem = this._getDomElementByIdentifier('selectSocialType');
    if (!elem) return;

    elem.addEventListener('click', function () {
      let insertTarget = this._getDomElementByIdentifier('feedsStorageInput'),
        randomIdentifier = Math.floor(Math.random() * 100000 + 1);

      insertTarget.attr('data-insert-target', randomIdentifier);
      this._openTypo3WinBrowser('db', randomIdentifier + '|||pages');
    });
  }

  /**
   * @private
   *
   * opens a popup window with the element browser
   *
   * @param mode
   * @param params
   */
  _openTypo3WinBrowser(mode, params) {
    const url = _getSetting('browserUrl') + '&mode=' + mode + '&bparams=' + params;
    Modal.advanced({
      type: Modal.types.iframe,
      content: url,
      size: Modal.sizes.large,
    });
  }

  /**
   * Get selector
   * @param elementIdentifier
   * @return {*|undefined}
   * @private
   */
  _getDomElementByIdentifier(elementIdentifier) {
    return document.querySelector(this._domElementsSelectors[elementIdentifier]) || undefined;
  }

  /**
   * Get insert target
   * @param reference
   * @return {HTMLElement|null}
   * @private
   */
  _getInsertTarget(reference) {
    return document.querySelector('[data-insert-target="' + reference + '"]');
  }

  /**
   * Get settings
   * @param key
   * @return {*|undefined}
   * @private
   */
  _getSetting(key) {
    return settings[key] || undefined;
  }
}

export default new SocialFeedAdministrationModule();
