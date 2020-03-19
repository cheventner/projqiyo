import { post } from './wordpressClient';

export function leadinConnectPortal(portalInfo) {
  return post('leadin_registration_ajax', portalInfo);
}

export function leadinDisconnectPortal() {
  return post('leadin_disconnect_ajax', {});
}

export function leadinResetReviewTimer() {
  return post('leadin_reset_review_ajax', {});
}

export function leadinRemoveReviewTimer() {
  return post('leadin_remove_review_ajax', {});
}
