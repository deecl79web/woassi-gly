<?php
/**
 * Woassi GLY — API pure JSON
 * Toutes les réponses sont du JSON, jamais de HTML.
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

// ── Env ───────────────────────────────────────────────────────────────────────
$DB_HOST     = getenv('DB_HOST')        ?: 'localhost';
$DB_NAME     = getenv('DB_NAME')        ?: 'postgres';
$DB_USER     = getenv('DB_USER')        ?: 'postgres';
$DB_PASSWORD = getenv('DB_PASSWORD')    ?: '';
$DB_PORT     = getenv('DB_PORT')        ?: '5432';
$ADMIN_PWD   = getenv('ADMIN_PASSWORD') ?: 'admin123';

// ── PDO ───────────────────────────────────────────────────────────────────────
function db(): PDO {
    global $DB_HOST,$DB_NAME,$DB_USER,$DB_PASSWORD,$DB_PORT;
    static $p=null;
    if(!$p){
        $dsn="pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};sslmode=require";
        $p=new PDO($dsn,$DB_USER,$DB_PASSWORD,[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        ]);
    }
    return $p;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function out(array $d,int $c=200):void{
    http_response_code($c);
    echo json_encode($d,JSON_UNESCAPED_UNICODE);
    exit;
}
function clean(string $v):string{
    return htmlspecialchars(trim($v),ENT_QUOTES,'UTF-8');
}
function isAdmin():bool{ return !empty($_SESSION['admin']); }

// ── Router ────────────────────────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if($action===''){
    out(['ok'=>false,'msg'=>'Aucune action spécifiée'],400);
}

// LOGIN
if($action==='login' && $method==='POST'){
    global $ADMIN_PWD;
    $b=json_decode(file_get_contents('php://input'),true)??[];
    if(($b['password']??'')===$ADMIN_PWD){
        $_SESSION['admin']=true;
        out(['ok'=>true]);
    }
    out(['ok'=>false,'msg'=>'Mot de passe incorrect'],401);
}

// LOGOUT
if($action==='logout'){
    session_destroy();
    out(['ok'=>true]);
}

// CHECK SESSION
if($action==='me'){
    out(['admin'=>isAdmin()]);
}

// INSCRIPTION PUBLIQUE
if($action==='inscrire' && $method==='POST'){
    $b=json_decode(file_get_contents('php://input'),true)??[];
    $nom   =clean($b['nom']       ??'');
    $prenom=clean($b['prenom']    ??'');
    $tel   =clean($b['telephone'] ??'');
    $sup   =clean($b['superficie']??'');
    if(!$nom||!$prenom||!$tel||!$sup)
        out(['ok'=>false,'msg'=>'Tous les champs sont requis'],400);
    $st=db()->prepare(
        "INSERT INTO inscriptions(nom,prenom,telephone,superficie) VALUES(?,?,?,?) RETURNING id"
    );
    $st->execute([$nom,$prenom,$tel,$sup]);
    out(['ok'=>true,'id'=>$st->fetch()['id']]);
}

// ── Admin protégé ─────────────────────────────────────────────────────────────
if(!isAdmin()) out(['ok'=>false,'msg'=>'Non autorisé'],403);

// LISTER
if($action==='lister'){
    $rows=db()->query("SELECT * FROM inscriptions ORDER BY created_at DESC")->fetchAll();
    out(['ok'=>true,'data'=>$rows]);
}

// AJOUTER
if($action==='ajouter' && $method==='POST'){
    $b=json_decode(file_get_contents('php://input'),true)??[];
    $nom   =clean($b['nom']       ??'');
    $prenom=clean($b['prenom']    ??'');
    $tel   =clean($b['telephone'] ??'');
    $sup   =clean($b['superficie']??'');
    if(!$nom||!$prenom||!$tel||!$sup)
        out(['ok'=>false,'msg'=>'Champs manquants'],400);
    db()->prepare(
        "INSERT INTO inscriptions(nom,prenom,telephone,superficie) VALUES(?,?,?,?)"
    )->execute([$nom,$prenom,$tel,$sup]);
    out(['ok'=>true]);
}

// MODIFIER
if($action==='modifier' && $method==='PUT'){
    $b=json_decode(file_get_contents('php://input'),true)??[];
    $id    =(int)($b['id']        ??0);
    $nom   =clean($b['nom']       ??'');
    $prenom=clean($b['prenom']    ??'');
    $tel   =clean($b['telephone'] ??'');
    $sup   =clean($b['superficie']??'');
    if(!$id||!$nom||!$prenom||!$tel||!$sup)
        out(['ok'=>false,'msg'=>'Données incomplètes'],400);
    db()->prepare(
        "UPDATE inscriptions SET nom=?,prenom=?,telephone=?,superficie=? WHERE id=?"
    )->execute([$nom,$prenom,$tel,$sup,$id]);
    out(['ok'=>true]);
}

// SUPPRIMER
if($action==='supprimer' && $method==='DELETE'){
    $b=json_decode(file_get_contents('php://input'),true)??[];
    $id=(int)($b['id']??0);
    if(!$id) out(['ok'=>false,'msg'=>'ID manquant'],400);
    db()->prepare("DELETE FROM inscriptions WHERE id=?")->execute([$id]);
    out(['ok'=>true]);
}

out(['ok'=>false,'msg'=>'Action inconnue'],404);
