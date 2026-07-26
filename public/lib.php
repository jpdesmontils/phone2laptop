<?php
$API_BASE='';
function render($defaul_page='',$blog_route=''){		
	
	// ---------- Inputs ----------
	$lang  = isset($_GET['lang'])  ? (($_GET['lang'] === 'eng') ? 'eng' : 'fr') : 'fr';
	$route = isset($_GET['route']) ? (string)$_GET['route'] : '';
	$route = preg_replace('~[^a-z0-9/_-]~i','', $route);
	$route = trim($route, '/');

	// rétro-compat blog/en
	if ($route === $blog_route . '/eng' || $route === $blog_route . '/en') {
	  $route = 'blog_en'; $lang = 'eng';
	}

	// ---------- Sélection du template ----------
	$template_file = ($route !== '') ? htmlspecialchars($route) . ".page" : $defaul_page;
	$template_file = str_replace("/.page",".page",$template_file);
	$template_file = str_replace($blog_route . '/', "", $template_file);
	if (!file_exists($template_file)) {
		$template_file = $defaul_page;
	}

	// ---------- Charger template brut ----------
	$template = file_get_contents($template_file);

	// ---------- Variables globales simples (remplacement {{var}}) ----------
	$global_vars = [
	  'lang'    => $lang,
	  'route'   => $route,
	  'API_BASE'=> $API_BASE,
	];
	$template = preg_replace_callback('/{{\s*(\w+)\s*}}/', function($m) use ($global_vars) {
	  $k = $m[1]; return isset($global_vars[$k]) ? $global_vars[$k] : $m[0];
	}, $template);

	// ---------- call-template : inclut un fragment local (HTML pur) ----------
	$template = preg_replace_callback('/<!--\s*call-template:([\w\-\.\_\/]+)\s*-->/', function($m) {
	  $file = $m[1];
	  if (!preg_match('~^[\w\-\._\/]+$~', $file)) return '';
	  return file_exists($file) ? file_get_contents($file) : '';
	}, $template);

	// ---------- call-xsl : applique un XSL à des sources JSON (locales ou distantes)
	// Syntaxe dans *.page : <!-- call-xsl:xsl/blog_list.xsl;data/blog.json -->
	// ou pour plusieurs sources: <!-- call-xsl:xsl/page.xsl;data/a.json,https://.../b.json -->
	$template = preg_replace_callback('/<!--\s*call-xsl:([^;]+);([^\s]+)\s*-->/', function($m) use ($lang, $route) {
	  $xslFile  = trim($m[1]);
	  $sources  = array_map('trim', explode(',', trim($m[2])));

	  if (!file_exists($xslFile)) return htmlspecialchars($xslFile) . ' non trouvé';

	  // 1) Construit le XML source
	  $root = new SimpleXMLElement('<root/>');

	  if (count($sources) === 1) {
		// Single JSON → injecter à la racine
		$data = fetch_json_maybe_remote($sources[0], 300);
		xml_add_array($data, $root);
	  } else {
		// Multiple JSON → un conteneur par source, nommé d’après le nom de fichier
		foreach ($sources as $src) {
		  $data  = fetch_json_maybe_remote($src, 300);
		  $label = label_from_path($src);
		  $child = $root->addChild($label);
		  xml_add_array($data, $child);
		}
	  }

	  if (!empty($_GET['debug']) && $_GET['debug'] === 'xsl') {
		// aide au debug : afficher le XML produit en commentaire HTML
		echo "<!--\n" . $root->asXML() . "\n-->";
	  }

	  // 2) Charger XML & XSL
	  $xmlDoc = new DOMDocument('1.0','UTF-8');
	  $xmlDoc->loadXML($root->asXML());

	  $xslDoc = new DOMDocument('1.0','UTF-8');
	  $xslDoc->load($xslFile);

	  // 3) Transformer
	  $proc = new XSLTProcessor();
	  if (method_exists($proc, 'setSecurityPrefs') && defined('XSL_SECPREF_READ_FILE')) {
		$proc->setSecurityPrefs(XSL_SECPREF_READ_FILE);
	  }
	  $proc->importStylesheet($xslDoc);

	  // 3.a) Paramètres utiles → XSL
	  $proc->setParameter('', 'lang',  $lang);
	  $proc->setParameter('', 'route', $route);
	  // Passer quelques GET courants (topic/id/page etc.) à l’XSL si présents
	  foreach (['topic','id','page','q'] as $p) {
		if (isset($_GET[$p]) && is_scalar($_GET[$p])) {
		  $proc->setParameter('', $p, (string)$_GET[$p]);
		}
	  }

	  $html = $proc->transformToXML($xmlDoc);
	  return $html ?: 'Erreur de transformation XSLT';
	}, $template);

	// ---------- Sortie ----------
	echo $template;
	
}
// =======================================================
// Helpers JSON/XSL
// =======================================================
function fetch_json_maybe_remote($path, $ttl = 300) {
  $isRemote = preg_match('~^https?://~i', $path);
  if (!$isRemote) {
    if (!file_exists($path)) return [];
    $j = file_get_contents($path);
    $a = json_decode($j, true);
    return (json_last_error() === JSON_ERROR_NONE && is_array($a)) ? $a : [];
  }
  // Remote with cache
  $cacheDir = __DIR__ . '/cache';
  if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0775, true); }
  $key  = md5($path);
  $file = $cacheDir . '/remote_' . $key . '.json';
  $noc  = isset($_GET['nocache']) ? (int)$_GET['nocache'] : 0;

  if (!$noc && file_exists($file) && (time() - filemtime($file) < $ttl)) {
    $j = file_get_contents($file);
    $a = json_decode($j, true);
    return (json_last_error() === JSON_ERROR_NONE && is_array($a)) ? $a : [];
  }
  // fetch distant
  $ch = curl_init($path);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  $body = curl_exec($ch);
  curl_close($ch);
  if ($body === false) { return []; }
  @file_put_contents($file, $body);
  $a = json_decode($body, true);
  return (json_last_error() === JSON_ERROR_NONE && is_array($a)) ? $a : [];
}

function xml_add_array($arr, SimpleXMLElement $xml) {
  foreach ((array)$arr as $k => $v) {
    $name = is_numeric($k) ? 'item' : preg_replace('~[^A-Za-z0-9_.-]~','_', (string)$k);
    if (is_array($v)) {
      $child = $xml->addChild($name);
      xml_add_array($v, $child);
    } else {
      $xml->addChild($name, htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }
  }
}

function label_from_path($p) {
  $u = parse_url($p, PHP_URL_PATH);
  if (!$u) $u = (string)$p;
  $b = basename($u);
  if ($b === '' || $b === '.' || $b === '/') $b = 'src';
  return preg_replace('~[^A-Za-z0-9_.-]~','_', $b);
}
