import $ from 'jquery';
import {
  leadinResetReviewTimer,
  leadinRemoveReviewTimer,
} from '../api/wordpressApi';

const hideNoticeAndRemoveTimer = container => {
  container.toggle();
  leadinRemoveReviewTimer();
};

export const createNoticeListeners = () => {
  const container = $('#leadin_feedback_notice');
  if (container.length) {
    $('#leadin_feedback_link').click(() => hideNoticeAndRemoveTimer(container));

    $('#leadin_dismiss_feedback_link').click(() => {
      container.toggle();
      leadinResetReviewTimer();
    });

    $('#leadin_remove_feedback_link').click(() =>
      hideNoticeAndRemoveTimer(container)
    );
  }
};
