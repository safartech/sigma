<nav class="navbar navbar-default navbar-fixed-top be-top-header">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#"></a>
        </div>

        <div class="">
            <ul class="nav navbar-nav navbar-right hidden-xs">
                <li><a href="http://www.sigma.com">SIGMA <img width="30" src="{{ asset('assets/img/avatar.png') }}" alt="Avatar"> </a></li>
            </ul>
            <div class="nav-ajust">
                <div class="page-title badge badge-info" ><span>{{ Auth()->user()->name }}</span></div>
            </div>
        </div>
    </div>
</nav>


