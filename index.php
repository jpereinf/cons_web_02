<?php
// Primeiro Passo: incluir autoload do composer
require __DIR__."/vendor/autoload.php";

use Symfony\Component\HttpFoundation\Request;

// Segundo Passo: instanciar o objeto da aplicação
$app = new Silex\Application();



// Terceiro passo: definir parämetros e serviços
$app["debug"] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/template',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => array (
        'mysql_read' => array(
            'driver'    => 'pdo_mysql',
            'host'      => '127.0.0.1',
            'dbname'    => 'consweb01',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8mb4',
        ),

    ),
));


$app->register(new Silex\Provider\RoutingServiceProvider());


// Quarto passo: definir as rotas e controladores
$app->get("/", function() use ($app){
    return $app->redirect("./list");
});

$app->match("/list", function(Request $request) use ($app){

    $sql = "select * from blog";
    $blog = $app["db"]->fetchAll($sql);

    return $app["twig"]->render("list.html", ["lista" => $blog]);
})->bind("list");

$app->match("/create", function(Request $request) use($app){
    return $app["twig"]->render("create.html");
})->bind("create");

$app->match("/insert", function(Request $request) use ($app){
    $blog = $request->request->all();

    $app["db"]->insert("blog", $blog);

    return $app->redirect($app["url_generator"]->generate('list'));
})->bind("insert");

$app->match("/remove/{ID}", function($id) use ($app){
    $app["db"]->delete("blog", ["ID" => $id]);

    return $app->redirect($app["url_generator"]->generate("list"));
});

$app->match("/edit/{ID}", function($id)use($app){
    $blog = $app["db"]->fetchAssoc("select * from blog where ID = ".$id);

    return $app["twig"]->render("edit.html", ["blog" => $blog]);
});

$app->match("/update", function(Request $request) use ($app){
    $app["db"]->update("blog", $request->request->all(),["ID" => $request->get("ID")]);

    return $app->redirect($app["url_generator"]->generate("list"));
})->bind("update");


// Quinto passo: executar a aplicação
$app->run();