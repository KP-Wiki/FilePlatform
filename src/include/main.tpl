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
        <link rel="stylesheet" href="/css/formValidation.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" crossorigin="anonymous"></script>
        <script src="/js/jquery.fileDownload.js"></script>
        <script src="/js/bootstrap-table-export.js"></script>
        <script src="/js/tableExport.js"></script>
        <script src="/js/formValidation.min.js"></script>
        <script src="/js/framework/bootstrap.min.js"></script>
        <script src="/js/reCaptcha2.min.js"></script>
        <script src="/js/bootstrap-filestyle.min.js"></script>
        <script src="/js/starrr.js"></script>
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
                                    <form method="post" action="/login" id="loginFrm" name="loginFrm" class="logreg-frm">
                                        <ul>
                                            <li>Username or Email</li>
                                            <li>
                                                <input type="text" placeholder="Username/Email" id="username" name="username" class="form-control" required>
                                            </li>
                                            <li>Password</li>
                                            <li>
                                                <input type="password" placeholder="Password" id="password" name="password" class="form-control" required>
                                            </li><br />
                                            <li>
                                                <button type="submit" id="logBtn" class="btn btn-default">Submit</button>
                                            </li>
                                        </ul>
                                    </form>
                                    <div class="clearfix"></div>
                                    <form method="post" action="/forgot" id="forgotFrm" name="forgotFrm" class="logreg-frm">
                                        <ul>
                                            <li>
                                                <a class="forgot-link" onclick="toggleForgot();" href="javascript:;">Forgot your password?</a>
                                                <div class="logreg-forgot">
                                                    <ul>
                                                        <li><p>Enter your Email Address here to receive a link to change password.</p></li>
                                                        <li>Email</li>
                                                        <li>
                                                            <input type="email" placeholder="Your email" id="forgetemail" class="form-control" name="forgetemail" required>
                                                        </li>
                                                        <li>
                                                            <button type="submit" class="btn btn-default">Send Mail</button>
                                                        </li>
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
                                    <form method="post" action="/register" id="userRegisterFrm" name="userRegisterFrm" class="logreg-frm">
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
                                            <li>
                                                <div id="captchaContainer"></div>
                                            </li><br />
                                            <li>
                                                <button type="submit" id="userRegBtn" name="userRegBtn" class="btn btn-default">Signup Now</button>
                                                <button type="button" id="userRegResetBtn" name="userRegResetBtn" class="btn btn-default">Reset</button>
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
                    [@header]
                    [@content]
                </div>
            </div>
        </div>
        <footer class="navbar-default navbar-fixed-bottom footer">
            <div class="row vertical-center">
                <div class="col-xs-3">
                    <a href="http://validator.w3.org/check?uri=referer;ss=1" style="margin-left: 15px;">
                        <img src="https://www.w3.org/html/logo/badge/html5-badge-h-css3-semantics.png" width="98px" height="38px" alt="HTML5 Powered with CSS3 / Styling, and Semantics" title="HTML5 Powered with CSS3 / Styling, and Semantics">
                    </a>
                </div>
                <div class="col-xs-6">
                    <center>
                        <span class="text-muted">Proudly created and maintained by KP-Wiki.org</span>
                    </center>
                </div>
                <div class="col-xs-3" style="text-align: right; margin-right: 15px;">
                    <a href="https://maps-docs.kp-wiki.org/" target="_blank">API docs</a>
                </div>
            </div>
        </footer>
    </body>
</html>
