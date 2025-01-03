<?php
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// Get application and parameters
$app = Factory::getApplication();
$option = $app->input->get('option');
$view = $app->input->get('view');
$currentArticleId = null;

// Get selected categories from module parameters
$allowedCategories = $params->get('category_id', array());

// Check if we're in an article view
if ($option === 'com_content' && $view === 'article') {
    $currentArticleId = $app->input->getInt('id');
} else {
    // Try to get from active menu item
    $menu = $app->getMenu();
    $active = $menu->getActive();
    if ($active) {
        $currentArticleId = $active->getParams()->get('article_id');
    }
}

// If we have an article ID, check category and get the intro image
if ($currentArticleId) {
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('a.images, a.catid')
        ->from('#__content AS a')
        ->where('a.id = ' . (int)$currentArticleId);
    $db->setQuery($query);
    $article = $db->loadObject();
    
    // Check if article exists and is in allowed category
    if ($article && (empty($allowedCategories) || in_array($article->catid, $allowedCategories))) {
        $images = json_decode($article->images);
        if (isset($images->image_intro) && !empty($images->image_intro)) {
            $introImageSrc = htmlspecialchars($images->image_intro);
            $introImageAlt = isset($images->image_intro_alt) ? 
                htmlspecialchars($images->image_intro_alt) : '';
            
            // Output the image
            echo '<div class="current-article-intro-image">';
            echo '<img src="' . $introImageSrc . '" alt="' . $introImageAlt . '" />';
            echo '</div>';
        }
    }
}
?>
