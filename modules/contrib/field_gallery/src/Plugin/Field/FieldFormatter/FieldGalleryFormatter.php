<?php

namespace Drupal\field_gallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

// EXAMPLE FROM :
// ImagefieldSlideshowFieldFormatter
// module : imagefield_slideshow.
/**
 * Plugin implementation of the 'multivaluefield' field formatter.
 *
 * @FieldFormatter(
 *   id = "field_gallery_formatter",
 *   label = @Translation("Field Gallery"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class FieldGalleryFormatter extends ImageFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountProxyInterface $current_user, ImageStyleStorageInterface $image_style_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currentUser = $current_user;
    $this->imageStyleStorage = $image_style_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get('current_user'), $container->get('entity.manager')
        ->getStorage('image_style')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
      'field_gallery_style' => 'large',
      'field_gallery_mainimglink' => TRUE,
      'field_gallery_prev_next' => TRUE,
      // Display thumbnails.
      'field_gallery_thumb' => TRUE,
      // Display thumbnails.
      'field_gallery_thumb_style' => 'thumbnail',
      // Maxium thumbnails.
      'field_gallery_thumb_max' => 5,
      // Display type
      // HTML or AJAX.
      'field_gallery_type' => 'html',
      // Previous and Next buttons texts.
      'text_prev' => 'Previous',
      'text_next' => 'Next',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $image_styles = image_style_options(FALSE);
    $description_link = Link::fromTextAndUrl(
        $this->t("Configure Image Styles"), Url::fromRoute('entity.image_style.collection')
    );
    $elements['field_gallery_style'] = [
      '#title' => $this->t("Image style"),
      '#type' => 'select',
      '#default_value' => $this->getSetting('field_gallery_style'),
      '#empty_option' => $this->t("None (original image)"),
      '#options' => $image_styles,
      '#description' => $description_link->toRenderable() + [
        '#access' => $this->currentUser->hasPermission('administer image styles'),
      ],
    ];

    // Thumbnails
    // field_gallery_thumb_style.
    $elements['field_gallery_thumb'] = [
      '#title' => $this->t("Display Thumbnails"),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('field_gallery_thumb'),
    ];
    $elements['field_gallery_thumb_style'] = [
      '#title' => $this->t("Thumbnails style"),
      '#type' => 'select',
      '#default_value' => $this->getSetting('field_gallery_thumb_style'),
      '#empty_option' => $this->t("None (original image)"),
      '#options' => $image_styles,
      '#description' => $description_link->toRenderable() + [
        '#access' => $this->currentUser->hasPermission('administer image styles'),
      ],
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][field_gallery_thumb]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['field_gallery_thumb_max'] = [
      '#title' => $this->t("Maxium number of thumbnails"),
      '#type' => 'number',
      '#default_value' => $this->getSetting('field_gallery_thumb_max'),
      '#min' => 0,
      '#description' => $this->t('Number = 0 = Not limited'),
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][field_gallery_thumb]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $display_types = self::getDisplayTypesList();
    $elements['field_gallery_type'] = [
      '#title' => $this->t('Display type'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('field_gallery_type'),
      '#options' => $display_types,
    ];
    $elements['field_gallery_mainimglink'] = [
      '#title' => $this->t('Next link on main image'),
      '#description' => $this->t('Show next (or first if last) image on click on the main image.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('field_gallery_mainimglink'),
    ];
    $elements['field_gallery_prev_next'] = [
      '#title' => $this->t('Display Next and Previus'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('field_gallery_prev_next'),
    ];
    $elements['text_prev'] = [
      '#title' => $this->t("Previous button text"),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('text_prev'),
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][field_gallery_prev_next]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['text_next'] = [
      '#title' => $this->t("Next button text"),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('text_next'),
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][field_gallery_prev_next]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $elements;
  }

  /**
   * Get field display types list.
   */
  public static function getDisplayTypesList() {
    return [
      'html' => "HTML",
      // 'ajax' => "AJAX", // TODO.
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $display_types = self::getDisplayTypesList();
    $summary[] = $this->t('Gallery @type (@style).', [
      '@type' => $display_types[$this->getSetting('field_gallery_type')],
      '@style' => $this->getSetting('field_gallery_style'),
    ]);
    $summary[] = $this->t('@thumb @pn', [
      '@thumb' => $this->getSetting('field_gallery_thumb') ? 'Thumb' : '',
      '@pn' => $this->getSetting('field_gallery_prev_next') ? 'P&N' : '',
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    // Not to cache this field formatter.
    $elements['#cache']['max-age'] = 0;
    $elements['#attributes']['class'] = ['field-gallery'];
    $elements['#attached']['library'][] = 'field_gallery/field_gallery.style';

    // Count nb items.
    $nb_items = $items->count();

    if (!$nb_items) {
      return $elements;
    }

    // Settings for theme template.
    $parent_entity = $items->getEntity();
    $theme_settings = [];
    $theme_settings['#field_name'] = $items->getName();
    $theme_settings['#entity_type'] = $parent_entity->getEntityTypeId();
    $theme_settings['#bundle'] = $parent_entity->bundle();
    $text_prev = $this->getSetting('text_prev');
    $text_next = $this->getSetting('text_next');

    // Get parent entity url.
    $parent_url = Url::fromRoute('<current>');

    // Get main image style.
    $image_style_setting = $this->getSetting('field_gallery_style');
    $image_main = NULL;
    $image_main_index = (int) (isset($_GET['index']) ? $_GET['index'] : 0);
    $image_style_main = NULL;
    if (!empty($image_style_setting)) {
      $image_style_main = ImageStyle::load($image_style_setting);
    }

    // Get thumbnails.
    $field_gallery_thumb = $this->getSetting('field_gallery_thumb');
    $field_gallery_thumb_max = $this->getSetting('field_gallery_thumb_max') ?: $nb_items;
    $image_style_setting = $this->getSetting('field_gallery_thumb_style');
    $image_style_thumb = NULL;
    if (!empty($image_style_setting)) {
      $image_style_thumb = ImageStyle::load($image_style_setting);
    }

    // Create thumnails pager.
    $images_thumb = [];
    $buttons = (int) $field_gallery_thumb_max;
    $currentPage = $lowerLimit = $upperLimit = min($image_main_index, $nb_items);

    // Search boundaries.
    for ($b = 0; $b < $buttons && $b < $nb_items;) {
      if ($lowerLimit > 0) {
        $lowerLimit--;
        $b++;
      }
      if ($b < $buttons && $upperLimit < $nb_items) {
        $upperLimit++;
        $b++;
      }
    }

    // Build thumnails list.
    for ($index = $lowerLimit; $index < $upperLimit; $index++) {

      // $items[$index];.
      $item = $items->get($index);
      if (!$item) {
        break;
      }

      $image_file = $item->entity->getFileUri();

      // Is current.
      $class = '';
      if ($index == $currentPage) {
        // Set main image.
        if ($image_style_main) {
          $image_main = $image_style_main->buildUrl($image_file);
        }
        else {
          // Get absolute path for original image.
          $image_main = $item->entity->url();
        }
        $class .= " active";
        if (!$field_gallery_thumb) {
          break;
        }
      }
      // Add thumnails.
      if ($image_style_thumb) {
        $image_uri = $image_style_thumb->buildUrl($image_file);
      }
      else {
        // Get absolute path for original image.
        $image_uri = $item->entity->url();
      }

      // Add thumbnail.
      $images_thumb[] = [
        'src' => $image_uri,
        'alt' => $item->getValue()['alt'],
        'class' => $class,
        'href' => $parent_url->setRouteParameter('index', $index)->toString(),
      ];
    }

    // Remobe vhomnails of no need.
    if (!$field_gallery_thumb) {
      $images_thumb = [];
    }

    // Enable prev next if only more than one image.
    $prev_next = $this->getSetting('field_gallery_prev_next');

    // Set next / Previus index.
    $index_prev = $image_main_index ? $image_main_index - 1 : 0;
    $index_next = ($image_main_index + 1) < $nb_items ? ($image_main_index + 1) : 0;

    // Link on main image.
    $mainimglink = $this->getSetting('field_gallery_mainimglink');

    $elements[] = [
      '#theme' => 'field_gallery',
      '#image' => $image_main,
      '#thumbnails' => $images_thumb,
      '#prev_next' => $prev_next,
      '#url' => $parent_url->setRouteParameter('index', 0)->toString(),
      '#url_next' => $parent_url->setRouteParameter('index', $index_next)->toString(),
      '#url_prev' => $parent_url->setRouteParameter('index', $index_prev)->toString(),
      '#is_fst' => $index_prev == 0,
      '#is_lst' => ($image_main_index + 1) == $nb_items,
      '#index' => $image_main_index,
      '#text_prev' => $text_prev,
      '#text_next' => $text_next,
      '#mainimglink' => $mainimglink,
      '#settings' => $theme_settings,
    ];

    return $elements;
  }

}
