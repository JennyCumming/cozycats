<?php

namespace Drupal\field_gallery\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test install and uninstall Entity Update module.
 *
 * @group Field
 */
class FieldGalleryInstallUninstallTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['field_gallery'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $permissions = [
      'access administration pages',
      'administer modules',
    ];

    // User to set up entity_update.
    $this->admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Tests if the module cleans up the disk on uninstall.
   */
  public function testFieldGalleryUninstall() {

    // Uninstall the module field_gallery.
    $edit = [];
    $edit['uninstall[field_gallery]'] = TRUE;
    $this->drupalPostForm('admin/modules/uninstall', $edit, t('Uninstall'));
    $this->assertText('Field Gallery', 'Field Gallery listed on the module uninstall confirmation page.');
    $this->drupalPostForm(NULL, NULL, t('Uninstall'));
    $this->assertText(t('The selected modules have been uninstalled.'), 'Modules status has been updated.');

  }

}
