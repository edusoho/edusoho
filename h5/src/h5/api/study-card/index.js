export default [
  {
    // 通过卡密获取学习卡信息
    name: 'getMoneyCardByPassword',
    url: '/plugins/moneyCard/moneyCard/{password}',
    method: 'GET'
  }, {
    // 根据token获取批次信息
    name: 'getMoneyCardByToken',
    url: '/plugins/moneyCard/moneyCardBatch/{token}',
    method: 'GET'
  }, {
    // 通过卡密充值学习卡
    name: 'chargeMoneyCardByPassword',
    url: '/plugins/moneyCard/moneyCard/{password}/receive',
    method: 'POST'
  }, {
    // 通过token充值学习卡
    name: 'chargeMoneyCardByToken',
    url: '/plugins/moneyCard/moneyCardBatch/{token}/receive',
    method: 'POST'
  }, {
    name: 'getCoin',
    url: '/setting/coin',
    method: 'GET'
  }, {
    name: 'getCash',
    url: '/me/cashAccount',
    method: 'GET'
  }
];
