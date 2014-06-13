var app = angular.module('mainApp', ['ui.bootstrap']);

app.factory('_', ['$window',
      function($window) {
        // place lodash include before angular
        return $window._;
      }
    ]);

app.factory('mainSvc', function($q, $http) {
    var ebayObj = {};

    var searchEbay = function(url) {
      var deferred = $q.defer();
        var response = $http({method: 'GET', url: url, timeout: 60000});
        response
        .success(function(response, status, headers, config){
            if(existy(response.ack)){
                if (response.ack == 'Success'){                    
                    if(!response.searchResult.item){
                        ebayObj.searchEbayMessage = '0 results found';
                        ebayObj.searchEbayResult = [];
                    }else{
                        ebayObj.searchEbayMessage = '';
                        if(_.isArray(response.searchResult.item)){
                            ebayObj.searchEbayResult = response.searchResult.item;
                        }else{
                            ebayObj.searchEbayResult = [];
                            ebayObj.searchEbayResult.push(response.searchResult.item);
                        }
                    }
                }else{
                    ebayObj.searchEbayMessage = 'There is a problem with the search: eBay API searching failed'+ response;
                }
            }else{
                ebayObj.searchEbayMessage = 'There is a problem with the search. Failed to open stream: HTTP request failed!'+ response;
            }
            deferred.resolve(response);
        })
        .error(function(response){
            ebayObj.searchEbayMessage = 'There is a problem with the search: '+response;
        });

      return deferred.promise;
    };
     
      function resetEbayObj(){
          ebayObj = {searchEbayMessage: "",searchEbayResult:{}}
      }
      resetEbayObj();
      return{
          searchEbay: searchEbay
          ,getEbayObj: function(){ return ebayObj; }
          ,setEbayObj: function(value){ ebayObj=value; }
          ,resetEbayObj: resetEbayObj
      };
});

app.directive('loading', function () {
  return {
    restrict: 'E',
    replace:true,
    template: '<div class="loading"><img src="http://www.nasa.gov/multimedia/videogallery/ajax-loader.gif" width="20" height="20" />Loading data, Please wait...</div>',
    link: function (scope, element, attr) {
          scope.$watch('loading', function (val) {
              if (val)
                  $(element).show();
              else
                  $(element).hide();
          });
    }
  }
});

app.controller('mainCtrl', ['$scope', '$http', '_', 'mainSvc', '$sce', function($scope, $http, _, mainSvc, $sce) {
   
  $scope.mainForm = {};
  $scope.mainForm.city="Portland";
  $scope.mainForm.CraigsURL = '';

  $scope.trustSrc = function(src) {
    return $sce.trustAsResourceUrl(src);
  }

  $scope.clickSearch = function() {
    if (!existy($scope.mainForm.searchText) || $scope.mainForm.searchText == "") return;
    $scope.loading = true;

    $scope.mainForm.CraigsURL = 'http://'+$scope.mainForm.city+'.craigslist.org/search/sss?query='+$scope.mainForm.searchText;

    var url = 'api_json.php?target=ebay&searchClause='+$scope.mainForm.searchText+'|'+
            $scope.mainForm.minPrice+'|'+$scope.mainForm.maxPrice+'|'+$scope.mainForm.freeShipping;

    mainSvc.searchEbay(url).then(function(result){

        var resultObj = mainSvc.getEbayObj();
        $scope.message = resultObj.searchEbayMessage;
        $scope.mainForm.items = resultObj.searchEbayResult;
        $scope.loading = false;
       // console.log($scope.mainForm.items);
    });
  }

}]);


//utilities
function existy(x) { return x != null }
function truthy(x) { return (x !== false) && existy(x) }
function not(x) { return !x }
function alwaysTrue() { return true; }
function alwaysFalse() { return false; }

function doWhen(cond, action) {
  if(truthy(cond))
    return action();
  else
    return undefined;
}

function cat() {
  var head = _.first(arguments);
  if (existy(head))
    return head.concat.apply(head, _.rest(arguments));
  else
    return [];
}

function construct(head, tail) {
  return cat([head], _.toArray(tail));
}

function mapcat(fun, coll) {
  return cat.apply(null, _.map(coll, fun));
}

function partial(fun /*, pargs */) {
  var pargs = _.rest(arguments);
  return function(/* arguments */) {
    var args = cat(pargs, _.toArray(arguments));
    return fun.apply(fun, args);
  };
}

function fnull(fun /*, defaults */) {
  var defaults = _.rest(arguments);
  return function(/* args */) {
    var args = _.map(arguments, function(e, i) {
      return existy(e) ? e : defaults[i];
    });
    return fun.apply(null, args); };
}
var safeMult = fnull(function(total, n) { return total * n }, 1, 1);
var safeDiv = fnull(function(total, n) { return total / n }, 1, 1);

function toTitleCase(str){
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

// data table utilities
function project(table, keys) {
  return _.map(table, function(obj) {
    return _.pick.apply(null, construct(obj, keys));
  });
}

function rename(obj, newNames) {
  return _.reduce(newNames, function(o, nu, old) {
    if (_.has(obj, old)) {
      o[nu] = obj[old];
      return o;
    }
    else
      return o;
  },
  _.omit.apply(null, construct(obj, _.keys(newNames))));
}

function as(table, newNames) {
  return _.map(table, function(obj) {
    return rename(obj, newNames);
  });
}

function restrict(table, pred) {
  return _.reduce(table, function(newTable, obj) {
    if (truthy(pred(obj)))
      return newTable;
    else
      return _.without(newTable, obj);
  }, table);
}