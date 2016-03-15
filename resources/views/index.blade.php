<!-- resources/views/index.php -->

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Tracker</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
</head>
<body ng-app="jobTrackerApp">

<div class="container">
    <div ui-view></div>
</div>

</body>

<!-- Application Dependencies -->
<script src="{{asset('libs/angular/angular.js')}}"></script>
<script src="{{asset('libs/angular-ui-router/release/angular-ui-router.js')}}"></script>
<script src="{{asset('libs/satellizer/satellizer.js')}}"></script>

<!-- Application Scripts -->
<script src="{{asset('assets/scripts/app.js')}}"></script>
<script src="{{asset('assets/scripts/authController.js')}}"></script>
<script src="{{asset('assets/scripts/userController.js')}}"></script>
</html>