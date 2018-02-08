<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AGIS Add-on</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    <link rel="stylesheet" href="//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/select2/select2.min.css') }}">

    <style>
        html, body {
            overflow-x: hidden;
        }
        body {
            font-family: 'Lato';
             /*background-color: #e7ecea;*/
        }

        .fa-btn {
            margin-right: 6px;
        }

        .navbar {
            position:fixed;
            top:0;
            left:0;
            width:100%
        }

        section {
            margin-top:65px;
            margin-bottom:75px
        }

        footer {
            position:fixed;
            bottom:0;
            left:0;
            width:100%;
            height:50px;
            border-top:1px solid black;
            background-color:#fff;
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        }
        footer a {
            display:inline-block;
            line-height:50px;
            text-align:center;
            width:50%;
            float:left;
            color:#000;
        }
        footer a.active {
            display:inline-block;
            line-height:50px;
            text-align:center;
            width:50%;
            float:left;
            background-color:#1E90FF;
            color: #fff;
        }
        /*adding syahrul*/
        .navbar-brand
        {
            position: absolute;
            width: 100%;
            left: 0;
            font-weight: bold;
            text-align: center;
            margin:0 auto;
        }
        .navbar.navbar-default.navbar-static-top{
              /* background-color:#fff ;*/ 
          /* background-color: #017d78;*/
          background-color: #fff;
        }
        .navbar-default .navbar-brand {
             /*color:rgb(241, 126, 21);*/
            /*color:#fff;*/
            color: #696969;
        }
        .navbar-default .navbar-nav>li>a {
            /*color: rgb(241, 126, 21);*/
          /* color: #017d78;*/
          color: #696969;
            margin-left: 20px;
        }
        @media (max-width: 767px) {
             
        }
    </style>

    @yield('styles')
</head>
<body id="app-layout">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container-fluid">
            @if($items['page']['slug'] =='views')
            <div class="navbar-header">
                <!--Branding Image-->
                <a class="navbar-brand" href="#" >
                    {{--{{ strtoupper($items->title) }}--}}
                    AGIS VIEW
                </a>

                 <ul class="nav navbar-nav navbar-left">
                    <li>
                        <div class="col-md-2 pull-left" style="margin-top: 0; margin-bottom:0; width:100%; " >
                            <!-- Logo -->
                        <a href="{{ url('page/views') }}" class="logo">
                            <img src="{{ asset('images/logo-asiangames.png') }}" style="height: 50px;" />
                        </a>
                        </div>
                    </li>
                </ul>
            </div>
            @elseif($items['page']['presentations'][0]['component']['view']=='select_filter' | $items['page']['presentations'][0]['component']['view']=='data_table')
    

            <div class="container-fluid">
            <div class="navbar-header" style="width: 100%;">
           <div class="col-md-2 pull-left" style="margin-top: 0; margin-bottom:0; height: 10%; " >
                <!-- Logo -->
            <a href="{{ url('page/views') }}" class="logo">
                <img src="{{ asset('images/logo-asiangames.png') }}" style="height: 50px;" />
            </a>
            </div>
            <div class="col-md-2 pull-right"  style="margin-top: 17px; padding-right: 0;">
                    {{--<a href="#myModal2" class="modal-filter" data-toggle="modal" data-target="#myModal2"  style="color: rgb(241, 126, 21);">
                    Search & Filter
                        <i class="fa fa-filter fa-lg" aria-hidden="true"></i>
                    </a>--}}
                </div>
            </div>
            </div>

            @else
            <div class="navbar-header">
                <!-- Branding Image -->
                <a class="navbar-brand" href="#">
                    {{ strtoupper($items['page']['title']) }}
                </a>
                 <div class="col-md-2 pull-left" style="margin-top: 0; margin-bottom: 0;">
                   {{--<a href="#" onclick="goBack()" style="color: rgb(241, 126, 21);font-size: 20px;">
                       <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>--}}
                     <a href="{{ url('/') }}" class="logo">
                            <img src="{{ asset('images/logo-asiangames.png') }}" style="height: 50px;" />
                    </a>
                </div>
            </div>
            @endif
        </div>
    </nav>

    <section class="container-fluid">
        <!-- Content Wrapper. Contains page content -->
        <div class="row">
            @yield('content')
        </div>
    </section>

    {{--<footer>
        <a class="{{ $items->slug === 'tasks' ? 'active' : '' }}" href="{{ url('page/tasks') }}">
            TASKS
        </a>
        <a class="{{ $items->slug === 'views' ? 'active' : '' }}" href="{{ url('page/views') }}">
            VIEWS
        </a>

        <div class="clearfix"></div>
    </footer>--}}

    <!-- Javascript -->
    <!-- jQuery 2.2.3 -->
    <script src="{{ asset('vendor/adminlte/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>

    <!-- Bootstrap -->
    <script src="{{ asset('vendor/adminlte/plugins//bootstrap/js/bootstrap.min.js') }}"></script>

        <!-- Select2 -->
        <script src="{{ asset('vendor/adminlte/plugins/select2/select2.min.js') }}"></script>
    {{--<script src="{{ asset('js/app.js') }}"></script>--}}

    @yield('scripts')

    <script type="text/javascript">
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>