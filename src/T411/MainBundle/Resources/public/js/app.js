var app = angular.module('App', ['ngResource', 'ngTable'],function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

app.controller('SearchCtrl', function ($scope, $filter, $resource, ngTableParams) {
    $('#services').removeClass('hide');
    $('#error').removeClass('hide');
    $scope.loading = false;
    $scope.error = false;
    $scope.category = '';

    var torrentApi = $resource(
        $scope.urlRoot + 'rest/torrents.json',
        { callback: "JSON_CALLBACK" },
        { get: { method: "GET" }}
    );

    var data = [];
    $scope.tableParams = new ngTableParams(
        {
            page: 1,            // show first page
            count: 300,           // count per page
            sorting: {name: 'asc'}
        }, {
            counts: [],
            total: data.length, // length of data
            getData: function($defer, params) {
                var orderedData = params.sorting()
                    ? $filter('orderBy')(data, params.orderBy())
                    : data;
                $defer.resolve(orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count()));
            }
        }
    );

    $scope.submitSearch = function () {
        $scope.loading = true;
        $scope.error = false;
        torrentApi.get({q: $scope.keywords, cat: $scope.category}).$promise.
            then(function (json) {
                data = json.data;
                $scope.tableParams.reload();
                $scope.loading = false;
            }).catch(function (json) {
                $scope.loading = false;
                $scope.error = json.data.data;
            });
    }
});

app.controller('SuggestCtrl', function ($scope, $filter, $resource) {
    $('#services').removeClass('hide');
    $scope.loading = false;
    $scope.mark = 5;
    $scope.movies = [];
    $scope.error = false;
    $scope.page = 1;
    $scope.total_pages = 0;

    var torrentApi = $resource(
        $scope.urlRoot + 'rest/suggest/movies.json',
        {callback: "JSON_CALLBACK"},
        {get: {method: "GET"}}
    );

    $scope.submitSearch = function (page) {
        $scope.loading = true;
        $scope.movies = [];
        $scope.error = false;
        $scope.page = 'undefined' === typeof page ? 1 : page;

        torrentApi.get({
            page: $scope.page,
            year: $scope.year,
            mark: $scope.mark,
            'genres[]': [$scope.genre1, $scope.genre2]
        }).$promise.
            then(function (json) {
                $scope.movies = json.data.movies;
                $scope.total_pages =  json.data.total_pages;
                $scope.loading = false;
            }).catch(function () {
                $scope.error = json.data.data;
                $scope.loading = false;
            });
    }

    $scope.range = function(min, max, step){
        step = step || 1;
        var input = [];
        for (var i = min; i <= max; i += step) input.push(i);
        return input;
    };
});