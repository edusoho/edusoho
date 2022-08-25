import {encryptToBase64} from '../../libs/xxtea';

export default class Coordinate {
  getCoordinate(event, secret) {
    const channelCode = secret;
    const point = event.screenX + ',' + event.screenY;
    const encryptedPoint = encryptToBase64(point, channelCode);
    const fingerprint = '1' + '.' + encryptedPoint;
    return fingerprint;
  }
}
