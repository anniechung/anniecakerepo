<?php ?>
<!DOCTYPE html>
<html>
  <head>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/lodash.js"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.17/angular.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.11.0/ui-bootstrap-tpls.min.js"></script>
    <link rel="stylesheet" href="./static/main.css" />
    <script src="./static/script.js"></script>
  </head>

  <body ng-app="mainApp" ng-controller="mainCtrl">
    <h2>Annie Cake's Prototype</h2>
    <div ng-init="mainForm.freeShipping=true">
        <label style="margin-left:10px">
            <label>Craigslist City:<input type="text" style="width:100px; margin-left: 10px" ng-model="mainForm.city" placeholder="Portland"></label>
            <input id="chkfreeship" type="checkbox" ng-model="mainForm.freeShipping" style="margin-left:20px;"><label for="chkfreeship" style="margin-right:30px">Free Shipping</label>
            Price $ <input type="text" style="width:50px;" ng-model="mainForm.minPrice"> to $
            <input type="text" style="width:50px" ng-model="mainForm.maxPrice">            
        </label>
        
        <span style="margin-left:10px"><input type="text" ng-model="mainForm.searchText" placeholder="Searching...">
        <button ng-click="clickSearch()" class="btn btn-primary">Search</button></span>
    </div>
    <hr>

    <tabset>
        <tab heading="Result on Ebay">
            <div ng-show="message" style="color:red;margin-left: 10px">{{message}}</div>
            <loading></loading>

            <span ng-repeat="item in mainForm.items">
                <a href="{{item.viewItemURL}}" target="_blank"><img ng-src="{{item.galleryURL}}"><span style="color:red">${{item.sellingStatus.currentPrice}}</span>, {{item.title}}</a><br /><br />
            </span>
        </tab>
        <tab heading="Result on Craigslist">
            <loading></loading>
           <!--<iframe id="craigs" style="width:100%; height:600px" ng-src="{{mainForm.CraigsURL}}"></iframe>-->
            <iframe type="text/html" style="width:100%; height:600px" ng-src="{{trustSrc(mainForm.CraigsURL)}}" allowfullscreen frameborder="0"></iframe>
        </tab>
    </tabset>

  </body>

</html>