<?php

namespace Drupal\field_gallery\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test install and uninstall Entity Update module.
 *
 * @group Field
 */
class FieldGalleryUITest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'field_gallery'];

  /**
   * Node Type.
   *
   * @var string
   */
  protected $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    /*

    // User to set up entity_update.
    $permissions = [
    //      'access content',
    //      'administer content types',
    //      'administer node fields',
    //      'administer node form display',
    //      'administer node display',
    //      'bypass node access',
    'access content',
    'administer nodes',
    'administer content types',
    'bypass node access',
    'access administration pages',
    ];

    // User to set up entity_update.
    $this->admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->admin_user);


    // Create content type, with underscores.
    $type_name = Unicode::strtolower($this->randomMachineName(8)) . '_test';
    $type = NodeType::create([
    'name' => $type_name,
    'type' => $type_name,
    ]);
    $type->save();
    $this->nodeType = $type->id();
     */

    parent::setUp();

    $permissions = [
      'access administration pages',
    ];

    // User to set up entity_update.
    $this->admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->admin_user);
  }

  /**
   * Tests Pages for Anonymous users.
   */
  public function testPageAccess() {

    // Check home page.
    $this->drupalGet('');
    $this->assertResponse(200, 'Page :  Site homepage');
  }

  /**
   * Tests Pages for Anonymous users.
   */
  public function testNodeTypeConfig() {

    // Check home page.
    $this->drupalGet('');
    $this->assertResponse(200, 'Page :  Site homepage');
  }

}
