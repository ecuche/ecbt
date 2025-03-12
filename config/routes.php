<?php
$router = new Framework\Router;

// Homes Routes
$router->add('/', ["controller" => "homes", "method" => "index"]);
$router->add('/register', ["controller" => "homes", "method" => "register", "auth"=>false]);
$router->add('/forgot-password', ["controller" => "homes", "method" => "forgot-password", "auth"=>false]);
$router->add('/register-new-user', ["controller" => "homes", "method" => "register-new-user", "form"=> "post"]);
$router->add('/log-in-user', ["controller" => "homes", "method" => "log-in-user", "form"=> "post"]);
$router->add('/recover-account', ["controller" => "homes", "method" => "recover-account", "form"=> "post"]);
$router->add("/reset/password/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}/{hash:[a-zA-Z0-9]{64}}", ["controller" => "homes", "method" => "reset-password", "auth"=>false]);
$router->add("/password/reset/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}/{hash:[a-zA-Z0-9]{64}}", ["controller" => "homes", "method" => "password-reset", "form"=> "post"]);
$router->add("/activate/account/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}/{hash:[a-zA-Z0-9]{64}}", ["controller" => "homes", "method" => "activate-account"]);
$router->add('/logout', ["controller" => "homes", "method" => "log-out-user"]);
$router->add("find/test", ["controller" => "homes", "method" => "find-test"]);
$router->add("test/{code:[A-Z0-9]{6}}/search/result", ["controller" => "homes", "method" => "search-result"]);
$router->add("search/test", ["controller" => "homes", "method" => "search-test", "form"=>"post"]);

$router->add('/contact-us', ["controller" => "homes", "method" => "contact-us", "form"=> "post"]);
$router->add('/contact', ["controller" => "homes", "method" => "contact"]);
$router->add('/about-us', ["controller" => "homes", "method" => "about-us"]);
$router->add('/404', ["controller" => "homes", "method" => "e404"]);
$router->add('/500', ["controller" => "homes", "method" => "e500"]);
$router->add('/test', ["controller" => "homes", "method" => "test"]);

// Admin Routes
$router->add('/admin', ["namespace" => "Admin", "controller" => "admins", "method" => "dashboard", "auth"=>true]);
$router->add('/admin/dashboard', ["namespace" => "Admin", "controller" => "admins", "method" => "dashboard", "auth"=>true]);
$router->add('/admin/all-users', ["namespace" => "Admin", "controller" => "admins", "method" => "all-users", "auth"=>true]);
$router->add('/admin/instructors/all', ["namespace" => "Admin", "controller" => "admins", "method" => "all-instructors", "auth"=>true]);
$router->add('/admin/students/all', ["namespace" => "Admin", "controller" => "admins", "method" => "all-students", "auth"=>true]);
$router->add('/admin/admins/all', ["namespace" => "Admin", "controller" => "admins", "method" => "all-admins", "auth"=>true]);
$router->add('/admin/find-user', ["namespace" => "Admin", "controller" => "admins", "method" => "find-user", "auth"=>true]);
$router->add('/admin/get-user', ["namespace" => "Admin", "controller" => "admins", "method" => "get-user", "auth"=>true, 'form'=>'post']);
$router->add('/admin/all-papers', ["namespace" => "Admin", "controller" => "admins", "method" => "all-papers", "auth"=>true]);
$router->add('/admin/edit/paper/{code:[A-Z0-9]{6}}', ["namespace" => "Admin", "controller" => "admins", "method" => "edit-paper", "auth"=>true]);
$router->add('/admin/update/paper/{code:[A-Z0-9]{6}}', ["namespace" => "Admin", "controller" => "admins", "method" => "update-paper", "auth"=>true, 'form'=>'post']);
$router->add('/admin/paper/{code:[A-Z0-9]{6}}/questions-list', ["namespace" => "Admin", "controller" => "admins", "method" => "questions-List", "auth"=>true]);
$router->add('/admin/find-paper', ["namespace" => "Admin", "controller" => "admins", "method" => "find-paper", "auth"=>true]);
$router->add('/admin/get-paper', ["namespace" => "Admin", "controller" => "admins", "method" => "get-paper", "auth"=>true, 'form'=>'post']);
$router->add("/admin/edit/user/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}", ["namespace" => "Admin", "controller" => "admins", "method" => "edit-user", "auth"=>true]);
$router->add("/admin/update/user/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}", ["namespace" => "Admin", "controller" => "admins", "method" => "update-user", "auth"=>true, 'form'=>'post']);
$router->add("/admin/instructor/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}/papers", ["namespace" => "Admin", "controller" => "admins", "method" => "instructor-papers", "auth"=>true]);
$router->add("/admin/paper/{code:[A-Z0-9]{6}}/{id:\d+}/unban/question", ["namespace" => "Admin", "controller" => "admins", "method" => "unban-question", "auth"=>true, 'form'=>'post']);
$router->add("/admin/paper/{code:[A-Z0-9]{6}}/{id:\d+}/ban/question", ["namespace" => "Admin", "controller" => "admins", "method" => "ban-question", "auth"=>true, 'form'=>'post']);
$router->add("/admin/paper/{code:[A-Z0-9]{6}}/view/question/{id:\d+}", ["namespace" => "Admin", "controller" => "admins", "method" => "view-question", "auth"=>true]);



http://localhost/ecbt/



// Users Routes
$router->add("/dashboard", ["controller" => "users", "method" => "dashboard"]);
$router->add("/profile/update", ["controller" => "users", "method" => "update-profile", "auth"=>true]);
$router->add("/update/profile", ["controller" => "users", "method" => "profile-update", "form" => "post"]);
$router->add("/profile/view", ["controller" => "users", "method" => "view-profile", "auth"=>true]);
$router->add("/update/password", ["controller" => "users", "method" => "password-update", "auth"=>true]);
$router->add("/password/update", ["controller" => "users", "method" => "update-password", "form" => "post"]);
$router->add("/users/profile/{username:\w+([-+.+@']\w+)*}", ["controller" => "users", "method" => "profile"]);
$router->add('/users/word/{word:[\w-]+}', ["controller" => "users", "method" => "word"]);
$router->add("/users/emailexists/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}", ["controller" => "users", "method" => "emailExists"]);
$router->add('/users', ["controller" => "users", "method" => "index", "middleware" => "message|message|message"]);
$router->add('/users/show', ["controller" => "users", "method" => "show"]);

//students Routes
$router->add("student", ["controller" => "students", "method" => "dashboard",  "auth"=>true]);
$router->add("student/dashboard", ["controller" => "students", "method" => "dashboard",  "auth"=>true]);
$router->add("results/show/all", ["controller" => "students", "method" => "show-all-results",  "auth"=>true]);
$router->add("paper/{code:[A-Z0-9]{6}}/result/show", ["controller" => "students", "method" => "show-test-result",  "auth"=>true]);
$router->add("paper/{code:[A-Z0-9]{6}}/result/review", ["controller" => "students", "method" => "review-test-result",  "auth"=>true]);
$router->add("paper/{code:[A-Z0-9]{6}}/result/view/{id:\d+}", ["controller" => "students", "method" => "view-question-result",  "auth"=>true]);
$router->add("paper/{code:[A-Z0-9]{6}}/test/sheet", ["controller" => "students", "method" => "test-sheet",  "auth"=>true]);
$router->add("paper/{code:[A-Z0-9]{6}}/test/preview", ["controller" => "students", "method" => "test-preview",  "auth"=>true]);
$router->add("paper/{code:[A-Z0-9]{6}}/test/start", ["controller" => "students", "method" => "start-test"]);
$router->add("paper/{code:[A-Z0-9]{6}}/test/submit", ["controller" => "students", "method" => "submit-test",  "auth"=>true]);
$router->add("paper/test/prev-next", ["controller" => "students", "method" => "prev-next",  "auth"=>true, "form" => "post"]);
$router->add("paper/test/submit-option-selected", ["controller" => "students", "method" => "submit-option-selected",  "auth"=>true, "form" => "post"]);

//Instructor Routes
$router->add("instructor", ["controller" => "instructors", "method" => "dashboard",  "auth"=>true]);
$router->add("instructor/dashboard", ["controller" => "instructors", "method" => "dashboard",  "auth"=>true]);
$router->add("instructor/new-test", ["controller" => "instructors", "method" => "new-test",  "auth"=>true]);
$router->add("instructor/papers", ["controller" => "instructors", "method" => "papers-list",  "auth"=>true]);
$router->add("instructor/new-test/submit", ["controller" => "instructors", "method" => "insert-new-test",  "auth"=>true, "form"=>"post"]);
$router->add("search/mystudent/test-results/all", ["controller" => "instructors", "method" => "search-my-student",  "auth"=>true, "form"=>"post"]);

$router->add("instructor/paper/{code:[A-Z0-9]{6}}/edit", ["controller" => "instructors", "method" => "edit-paper",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/update", ["controller" => "instructors", "method" => "update-paper",  "auth"=>true, "form"=>"post"]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/list", ["controller" => "instructors", "method" => "questions-list",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/status/{status:[\w-]+}", ["controller" => "instructors", "method" => "change-paper-status",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/delete", ["controller" => "instructors", "method" => "delete-paper",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/participants/show", ["controller" => "instructors", "method" => "show-participants",  "auth"=>true]);

$router->add("instructor/paper/{code:[A-Z0-9]{6}}/create/questions", ["controller" => "instructors", "method" => "create-questions",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/add/question", ["controller" => "instructors", "method" => "add-question",  "auth"=>true, "form"=>"post"]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/{id:\d+}/edit/question", ["controller" => "instructors", "method" => "edit-question",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/{id:\d+}/update/question", ["controller" => "instructors", "method" => "update-question",  "auth"=>true, "form"=>"post"]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/{id:\d+}/delete/question", ["controller" => "instructors", "method" => "delete-question",  "auth"=>true]);
$router->add("instructor/paper/{code:[A-Z0-9]{6}}/{id:\d+}/delete-question-image", ["controller" => "instructors", "method" => "delete-question-image",  "auth"=>true]);
$router->add("instructor/my-students", ["controller" => "instructors", "method" => "my-students",  "auth"=>true]);
$router->add("student/{email:\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*}/instructor/tests", ["controller" => "instructors", "method" => "student-tests",  "auth"=>true]);

// Generic Controllers CRUD Routes
$router->add('/{controller}/show/{id:\d+}', ["method" => "show", "middleware"=>"deny"]);
$router->add('/{controller}/edit/{id:\d+}', ["method" => "edit"]);
$router->add('/{controller}/update/{id:\d+}', ["method" => "update", "form"=> "post"]);
$router->add('/{controller}/delete/{id:\d+}', ["method" => "delete"]);
$router->add('/{controller}/destroy/{id:\d+}', ["method" => "destroy", "form" => "post"]);

// Generic Routes
// $router->add("/{controller}/{method}");
// $router->add("{username:\w+([-+.+@']\w+)*}", ["controller" => "users", "method" => "profile"]);
// $router->add("/{controller}/{method}/{id:\d+}");
// $router->add('/{title}/{id:\d+}/{page:\d+}', ["controller" => "users", "method" => "showPage"]);

return $router;