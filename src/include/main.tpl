<!DOCTYPE html>
<html lang="en">
    <head>
        <title>[@title] - KP-Maps</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Maps for Knights Province, a 3D RTS game">
        <meta name="author" content="Thimo Braker - KP-Wiki">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/css/bootstrapValidator.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/main.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" crossorigin="anonymous"></script>
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <script src="/js/jquery.fileDownload.js"></script>
        <script src="/js/bootstrap-table-export.js"></script>
        <script src="/js/tableExport.js"></script>
        <script src="/js/main.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container-fluid">
                [@nav]
            </div>
        </nav>
        <div class="container-fluid">
            <!-- Begin Modal HTML -->
            <div id="loginModal" class="modal fade">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title">Login</h4>
                        </div>
                        <div class="modal-body">
                            <div class="col-md-12 col-sm-12 no-padng">
                                <div class="logreg-model">
                                    <form method="post" id="loginFrm" class="logreg-frm" name="loginFrm" role="form" data-toggle="validator">
                                        <ul>
                                            <li>Username or Email</li>
                                            <li><input type="text" placeholder="Username/Email" id="userName" name="userName" class="form-control" required></li>
                                            <li>Password</li>
                                            <li><input type="password" placeholder="Password" id="password" name="password" class="form-control" required></li><br />
                                            <li><button type="button" onclick="userLogin();" id="logBtn" class="btn btn-default">Submit</button></li>
                                        </ul>
                                    </form>
                                    <div class="clearfix"></div>
                                        <form method="post" id="forgotFrm" class="logreg-frm" name="forgotFrm">
                                        <ul>
                                            <li>
                                                <a class="forgot-link" onclick="toggleForgot();" href="javascript:;">Forgot your password?</a>
                                                <div class="logreg-forgot">
                                                    <ul>
                                                        <li><p>Enter your Email Address here to receive a link to change password.</p></li>
                                                        <li>Email</li>
                                                        <li><input type="email" placeholder="Your email" id="forgetemail" class="form-control" name="forgetemail" required></li>
                                                        <li><button type="button" class="btn btn-default" onclick="forgot();">Send Mail</button></li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="RegisterModal" class="modal fade">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title">Register</h4>
                        </div>
                        <div class="modal-body">
                            <div class="col-md-12 col-sm-12 no-padng">
                                <div class="logreg-model">
                                    <form method="post" id="userRegisterFrm" class="logreg-frm" name="userRegisterFrm">
                                        <ul>
                                            <li>Username</li>
                                            <li>
                                                <input type="text" id="username" name="username" placeholder="Username" class="form-control" required>
                                            </li>
                                            <li>Email</li>
                                            <li>
                                                <input type="email" id="emailAddress" name="emailAddress" placeholder="Email" class="form-control" required>
                                                <input type="email" id="confirmEmailAddress" name="confirmEmailAddress" placeholder="Confirm Email" class="form-control" style="display: none;">
                                            </li>
                                            <li>Password</li>
                                            <li>
                                                <input type="password" id="password" name="password" placeholder="Password" class="form-control" required>
                                            </li>
                                            <li>Confirm Password</li>
                                            <li>
                                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" class="form-control" required>
                                            </li><br />
                                            <!-- Need to find a nice way to load the sitekey from the config. -->
                                            <li>
                                                <div class="g-recaptcha" data-sitekey="6LcTcBkUAAAAADJcfF5RhZL_3I8ALCacwmHztWeu" data-callback="enableRegBtn" data-expired-callback="disableRegBtn"></div>
                                            </li><br />
                                            <li>
                                                <button type="button" id="userRegBtn" name="userRegBtn" class="btn btn-default">Signup Now</button>
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal HTML -->
            <div class="row">
                <div class="col-sm-9 col-sm-offset-2 col-md-10 col-md-offset-1 main">
                    <h1 class="page-header">[@title]</h1>
                    [@content]
                </div>
            </div>
        </div>
        <footer class="navbar-default navbar-fixed-bottom footer">
            <div class="container-fluid w3clogo">
                <a href="http://validator.w3.org/check?uri=referer;ss=1">
                    <img src="https://www.w3.org/html/logo/badge/html5-badge-h-css3-semantics.png" width="98px" height="38px" alt="HTML5 Powered with CSS3 / Styling, and Semantics" title="HTML5 Powered with CSS3 / Styling, and Semantics">
                </a>
            </div>
            <div class="container-fluid">
                <p class="text-muted">Proudly created and maintained by KP-Wiki.org</p>
            </div>
        </footer>
    </body>
</html>
