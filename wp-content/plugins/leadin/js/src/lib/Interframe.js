import connectToChild from 'penpal/lib/connectToChild';
import Raven from './Raven';
import { syncRoute, leadinPageReload, leadinPageRedirect } from '../navigation';
import * as leadinConfig from '../constants/leadinConfig';
import { leadinClearQueryParam, getQueryParam } from '../utils/queryParams';
import { leadinGetPortalInfo } from '../utils/portalInfo';
import {
  leadinConnectPortal,
  leadinDisconnectPortal,
} from '../api/wordpressApi';

const methods = {
  leadinClearQueryParam,
  leadinPageReload,
  leadinPageRedirect,
  leadinGetPortalInfo,
  leadinConnectPortal,
  leadinDisconnectPortal,
  getLeadinConfig: () => leadinConfig,
};

const hubspotBaseUrl = leadinConfig.hubspotBaseUrl;

function createConnectionToiFrame(iframe) {
  return connectToChild({
    // The iframe to which a connection should be made
    iframe,
    childOrigin: hubspotBaseUrl, // the plugin will reject all connections not coming from the iframe
    // Methods the parent is exposing to the child
    methods,
  });
}

export function initInterframe(iframe) {
  if (!iframe) return;

  if (!window.childFrameConnection) {
    window.childFrameConnection = createConnectionToiFrame(iframe);
    window.childFrameConnection.promise.catch(error =>
      Raven.captureException(error, {
        fingerprint: ['INTERFRAME_CONNECTION_ERROR'],
      })
    );
  }

  const redirectToLogin = event => {
    if (event.data === 'unauthorized') {
      window.removeEventListener('message', redirectToLogin);
      iframe.src = leadinConfig.loginUrl;
    }
  };

  const handleSyncRoute = event => {
    if (event.origin !== hubspotBaseUrl) return;
    try {
      const data = JSON.parse(event.data);
      if (data['leadin_sync_route']) {
        const route = data['leadin_sync_route'];
        syncRoute(route);
      }
    } catch (e) {
      // Error in parsing message
    }
  };

  const currentPage = getQueryParam('page');
  if (currentPage !== 'leadin_settings' && currentPage !== 'leadin') {
    window.addEventListener('message', redirectToLogin);
  }

  window.addEventListener('message', handleSyncRoute);
}
