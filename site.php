<?php
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Address;

$app->get('/', function(){

    $products = Product::listAll();

    $page = new Page();

    $page->setTpl("index", [
        'products' => Product::checklist($products)
    ]);

});

$app->get("/categories/:idcategory", function($idcategory){

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] :1;

    $category = new Category();

    $category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = [];

    for ($i=1; $i <=$pagination['pages'];$i++)
    {
        array_push($pages,[
            //antes da ? tem o caminho após a ? tem as variáveis
            'link' => '/categories/'.$category->getidcategory().'?page='.$i,
            'page' => $i

        ]);

    }

    $page = new Page();

    $page->setTpl("category", [
        'category' => $category->getValues(),
        'products' =>$pagination["data"]
    ]);

});

$app->get("/products/:desurl", function($desurl){

    $product = new Product();
    $product->getFromUrl($desurl);

    $page = new Page();

    $page->setTpl("product-detail", [
        //variáveis para carrega na template product-detail
       'product' => $product->getValues(),
       'categories' => $product->getCategories()
    ]);

});

$app->get("/cart", function(){


    $cart = Cart::getFromSession();
    $page = new Page();

    $page->setTpl("cart", [
        'cart' =>$cart->getValues(),
        'products' =>$cart->getProducts(),
        'error' =>Cart::getMsgError()
    ]);


});

$app->get("/cart/:idproduct/add", function($idproduct){

        $product = new Product();

        $product->get((int)$idproduct);

        $cart = Cart::getFromSession();

        $cart->addProduct($product);

        header("Location: /cart");
        exit;

});

$app->get("/cart/:idproduct/minus", function($idproduct){

        $product = new Product();

        $product->get((int)$idproduct);

        $cart = Cart::getFromSession();

        $cart->removeProduct($product);

        header("Location: /cart");
        exit;

});
$app->get("/cart/:idproduct/remove", function($idproduct){

        $product = new Product();

        $product->get((int)$idproduct);

        $cart = Cart::getFromSession();

        $cart->removeProduct($product, true);

        header("Location: /cart");
        exit;

});

$app->post("/cart/freight", function(){

        $cart = Cart::getFromSession();
        $cart->setFreight($_POST['zipcode']);

        header("Location: /cart");
        exit;

});

$app->get("/checkout", function(){

    User::verifyLogin(false);
    $cart = Cart::getFromSession();
    $address = new Address();
    $page = new Page();
    $page->setTpl("checkout", [

        'cart' => $cart->getValues(),
        'address' =>$address->getValues()
    ]);

});

$app->get("/login", function(){

    $page = new Page();
    $page->setTpl("login", [
        'error' =>User::getError(),
        'errorRegister' =>User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ?   $_SESSION['registerValues'] : ['name' => '', 'email' => '', 'phone' =>'']
    ]);
    //Cá está a busca dos valores de um registo; no register values;


});

$app->post("/login", function(){

    try {

    User::login($_POST['login'], $_POST['password']);

    }catch(Exception $e){

    User::setError($e->getMessage());

    }
    header("Location: /checkout");
    exit;

});

$app->get("/logout", function(){

    User::logout();

    header("Location: /login");
    exit;

});

$app->post("/register", function (){
    //Guarda os valores do registo de alguém, caso dê um erro quando se está a preencher o registo, depois os valores anteriormente preenchidos são colocados no formulário
    $_SESSION['registerValues'] = $_POST;
    //Validação, para obrigar a pessoa a escrever estes parâmetros, nesta caso o nome
    if( !isset($_POST['name'])|| $_POST['name'] == ''){

        User::setErrorRegister("Preencha o seu nome!");
        header("Location: /login");
        exit;
    }

    //validação para o email
    if( !isset($_POST['email'])|| $_POST['email'] == ''){

        User::setErrorRegister("Preencha o seu email!");
        header("Location: /login");
        exit;
    }

    //validação para a senha
    if( !isset($_POST['password'])|| $_POST['password'] == ''){

        User::setErrorRegister("Preencha a sua senha!");
        header("Location: /login");
        exit;
    }
    //validação se existe um login igual, não deixa

    if(User::checkLoginExist($_POST['email'])=== true){

        User::setErrorRegister("Este endereço de email já existe");
        header("Location: /login");
        exit;
    }

   $user = new User();

   $user->setData([
       'inadmin' => 0,
       'deslogin' => $_POST['email'],
       'desperson' => $_POST['name'],
       'desemail' => $_POST['email'],
       'despassword' => $_POST['password'],
       'nrphone' => $_POST['phone'],

   ]);

   $user->save();

   User::login($_POST['email'], $_POST['password']);

   header("Location: /checkout");
   exit;

});
