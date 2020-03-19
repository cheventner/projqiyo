import $ from 'jquery';
import { initInterframe } from '../lib/Interframe';
import { iframeUrl } from '../constants/leadinConfig';
import { createNoticeListeners } from '../utils/noticeListeners';

export const createIframe = () => {
  const container = $('#leadin-iframe-container');
  const $iframe = $(`<iframe id="leadin-iframe" src="${iframeUrl}"></iframe>`);
  initInterframe($iframe[0]);

  container.append($iframe);
  createNoticeListeners();
};
