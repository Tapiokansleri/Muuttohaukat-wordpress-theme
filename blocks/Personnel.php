<?php
namespace Muuttohaukat\Blocks;
 
class Personnel extends \Muuttohaukat\Block {
  public function getSettings() {
    $parent = parent::getSettings();
 
    return \Muuttohaukat\params($parent, [
      'category' => 'widgets',
    ]);
  }
 
  public function render($fields, $isPreview = false, $postId = 0) {
    $data = \Muuttohaukat\params(
      array_merge(
          \Muuttohaukat\getDefaultBlockRenderSettings(),
        [
          'listOffices' => false,
          'persons' => [], // list of IDs
 
        ]
      ),
      $fields
    );
 
    $officeUrl = function($office = 'Hyvinkää') {
      switch ($office) {
        case 'Hyvinkää':
          return '/hyvinkaa';
 
        case 'Kerava':
          return '/kerava';
        
        case 'Kirkkonummi':
          return '/kirkkonummi';
 
        case 'Kouvola':
          return '/kouvola';
 
        case 'Lohja':
          return '/lohja';
 
        default: 
          return '/yhteystiedot';
      }
    };
 
    $classes = [
      'personnel',
      'bg-transparent',
      'wrapper',
      'flex',
      'flex-wrap',
      'w-1/1',
      'items-center',
      'justify-center',
      'flex-row',
      'bg-transparent',
    ];
  ?>
 
<div <?=\Muuttohaukat\className(...$classes) ?>>
  <?php foreach ($data["persons"] as $id) {
      $name = \get_the_title($id);
      $imgId = \get_post_thumbnail_id($id);
 
      $x = \get_field('acf', $id);
      $title = $x['title'];
      $responsibilities = $x['responsibilities'];
      $phone = $x['phone'];
      $email = $x['email'];
      $office = $x['office'] ?? "Yleisesti";
 
      $image = !empty($imgId) ? \Muuttohaukat\Media\image($imgId, ['responsive' => false]) : null;
 
  ?>
  <div class="
      wrapper
      flex
      
      px-4
      mb-4
      items-stretch
      justify-center
      flex-col
      bg-transparent
      bg-cover
      bg-center
      
      md:w-1/2
      md:items-stretch
      md:justify-center
      md:flex-col
    
      xl:w-1/3
      xl:items-stretch
      xl:justify-center
      xl:flex-col">
    <?php if ($data['listOffices']) { ?>
      <a href="<?= esc_url($officeUrl($office)) ?>">
        <h2 class="has-text-align-center">
          <?= esc_html($office) ?>
        </h2>
      </a>
    <?php } ?>
 
    <div class="wp-block-image">
      <!-- Max width is artificially set because 99% of the images are 206px. -->
      <figure class="aligncenter size-full" style="max-width: 206px;">
        <?= $image ?>
      </figure>
    </div>
 
    <h3 class="has-text-align-center">
      <?= esc_html($name) ?>
    </h3>
 
    <p class="has-text-align-center">
      <em>
        <?= esc_html($title) ?>
      </em>
      <br>
      <?= esc_html($responsibilities) ?><br><br>
        <?= esc_html($phone) ?><br>
 
          <a href="mailto:<?= esc_attr($email) ?>" class="hover:text-brand">
            <?= esc_html($email) ?>
          </a>
    </p>
  </div>
  <?php } ?>
</div>
<?php
  }
}