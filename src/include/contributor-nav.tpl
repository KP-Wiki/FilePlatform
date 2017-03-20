                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/home">KP-Maps</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li [@home-active]><a href="/home" title="Home"><span class="glyphicon glyphicon-home"></span></a></li>
                        <li [@about-active]><a href="/about" title="About"><span class="glyphicon glyphicon-info-sign"></span></a></li>
                        <li><a href="http://knightsprovince.com" target="_blank" title="Go to the Knights Province website"><span class="glyphicon glyphicon-globe"></span>&nbsp;&nbsp;Knights Provice</a></li>
                        <li><a href="https://kp-wiki.org" target="_blank" title="Go to the KP-Wiki website"><span class="glyphicon glyphicon-globe"></span>&nbsp;&nbsp;KP-Wiki</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="/dashboard"><span class="glyphicon glyphicon-dashboard"></span>&nbsp;&nbsp;Dashboard</a></li>
                                <li><a href="/settings"><span class="glyphicon glyphicon-cog"></span>&nbsp;&nbsp;Settings</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="/logout"><span class="glyphicon glyphicon-log-out"></span>&nbsp;&nbsp;Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
