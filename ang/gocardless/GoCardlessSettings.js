(function(angular, $, _) {

  angular.module('gocardless').config(function($routeProvider) {
      $routeProvider.when('/gocardless', {
        controller: 'GocardlessGoCardlessSettings',
        controllerAs: '$ctrl',
        templateUrl: '~/gocardless/GoCardlessSettings.html',

        // If you need to look up data when opening the page, list it out
        // under "resolve".
        resolve: {
          various: function(crmApi4) {
            return crmApi4({
              gcSettings: ['Setting', 'get', {select: ['gocardless']}, 0],
              paymentProcessors: ['PaymentProcessor', 'get', {
                where: [
                  ["payment_processor_type_id:name", '=', 'GoCardless'],
                  ["is_test", 'IS NOT NULL'], // api4 seems to skip test ones otherwise
                ],
                orderBy: {
                  is_active: 'DESC',
                  name: 'ASC'
                }
              }],
            });
          },
        }
      });
    }
  );

  // The controller uses *injection*. This default injects a few things:
  //   $scope -- This is the set of variables shared between JS and HTML.
  //   crmApi, crmStatus, crmUiHelp -- These are services provided by civicrm-core.
  angular.module('gocardless').controller('GocardlessGoCardlessSettings', function($scope, crmApi4, crmStatus, crmUiHelp, various) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('uk.artfulrobot.civicrm.gocardless');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/gocardless/GoCardlessSettings'}); // See: templates/CRM/gocardless/GoCardlessSettings.hlp
    // Local variable for this controller (needed when inside a callback fn where `this` is not available).
    var ctrl = this;

    // Parse settings
    var gcSettings = {};
    console.log("GOT ", various);
    if (various.gcSettings.value) {
      gcSettings = JSON.parse(various.gcSettings.value);
      if (!gcSettings) {
        gcSettings = {};
      }
    }
    // Check settings have defaults.
    // Annoyingly this is duplicated from CRM_GoCardlessUtils::getSettings()
    const defaults = {
      forceRecurring: false,
      sendReceiptsForCustomPayments: 'never'
    };
    Object.keys(defaults).forEach(k => {
      if (!(k in gcSettings)) {
        gcSettings[k] = defaults[k];
      }
    });
    $scope.gcSettings = gcSettings;

    // Make pay processors accessible
    var ppTable = [];
    var ppNames = {};
    var urlStub = window.location.href.replace(/^(https?:\/\/[^/]+).*$/, '$1');
    various.paymentProcessors.forEach(pp => {
      var k;
      if (!(pp.name in ppNames)) {
        ppNames[pp.name] = { name: pp.name, p: pp};
      }
      if (pp.is_test) {
        k = 'urlTest';
      }
      else {
        k = 'urlLive';
      }

      // Make URL.
      ppNames[pp.name][k] = urlStub + CRM.url('civicrm/payment/ipn/' + pp.id, null, 'front');
    });
    $scope.ppTable = Object.values(ppNames);

    this.save = function() {
      return crmStatus(
        // Status messages. For defaults, just use "{}"
        {start: ts('Saving...'), success: ts('Saved')},
        // The save action. Note that crmApi() returns a promise.
        crmApi4('Setting', 'set', { values: { gocardless: JSON.stringify($scope.gcSettings)} })
      );
    };
  });

})(angular, CRM.$, CRM._);
