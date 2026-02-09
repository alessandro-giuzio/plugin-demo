<?php
// Basic single template for Employee Profile
get_header();

the_post();

$email = get_post_meta(get_the_ID(), '_employee_email', true);
$phone = get_post_meta(get_the_ID(), '_employee_phone', true);
$job_title = get_post_meta(get_the_ID(), '_employee_job_title', true);
$department = get_post_meta(get_the_ID(), '_employee_department', true);
$company = get_post_meta(get_the_ID(), '_employee_company', true);
$website = get_post_meta(get_the_ID(), '_employee_website', true);

$street = get_post_meta(get_the_ID(), '_employee_street', true);
$postcode = get_post_meta(get_the_ID(), '_employee_postcode', true);
$city = get_post_meta(get_the_ID(), '_employee_city', true);
$country = get_post_meta(get_the_ID(), '_employee_country', true);

$facebook = get_post_meta(get_the_ID(), '_employee_facebook', true);
$twitter = get_post_meta(get_the_ID(), '_employee_twitter', true);
$profile = get_post_meta(get_the_ID(), '_employee_profile', true);

$uuid = get_post_meta(get_the_ID(), '_ag_employee_uuid', true);
$download_url = admin_url('admin-ajax.php?action=ag_employee_vcard&uuid=' . rawurlencode($uuid));
?>

<div class="employee-profile">
  <h1>
    <?php the_title(); ?>
  </h1>

  <?php if (has_post_thumbnail()): ?>
    <div class="employee-photo">
      <?php the_post_thumbnail('medium'); ?>
    </div>
  <?php endif; ?>

  <div class="employee-basic">
    <?php if ($job_title): ?>
      <p><strong>Job Title:</strong>
        <?php echo esc_html($job_title); ?>
      </p>
    <?php endif; ?>
    <?php if ($department): ?>
      <p><strong>Department:</strong>
        <?php echo esc_html($department); ?>
      </p>
    <?php endif; ?>
    <?php if ($company): ?>
      <p><strong>Company:</strong>
        <?php echo esc_html($company); ?>
      </p>
    <?php endif; ?>
  </div>

  <div class="employee-contact">
    <?php if ($email): ?>
      <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($email); ?>">
          <?php echo esc_html($email); ?>
        </a></p>
    <?php endif; ?>
    <?php if ($phone): ?>
      <p><strong>Phone:</strong>
        <?php echo esc_html($phone); ?>
      </p>
    <?php endif; ?>
    <?php if ($website): ?>
      <p><strong>Website:</strong> <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener">
          <?php echo esc_html($website); ?>
        </a></p>
    <?php endif; ?>
  </div>

  <div class="employee-address">
    <?php if ($street || $postcode || $city || $country): ?>
      <p><strong>Address:</strong>
        <?php echo esc_html(trim($street . ', ' . $postcode . ' ' . $city . ', ' . $country, ', ')); ?>
      </p>
    <?php endif; ?>
  </div>

  <div class="employee-social">
    <?php if ($facebook): ?>
      <p><strong>Facebook:</strong> <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener">
          <?php echo esc_html($facebook); ?>
        </a></p>
    <?php endif; ?>
    <?php if ($twitter): ?>
      <p><strong>X:</strong> <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener">
          <?php echo esc_html($twitter); ?>
        </a></p>
    <?php endif; ?>
    <?php if ($profile): ?>
      <p><strong>Profile:</strong> <a href="<?php echo esc_url($profile); ?>" target="_blank" rel="noopener">
          <?php echo esc_html($profile); ?>
        </a></p>
    <?php endif; ?>
  </div>

  <div class="employee-content">
    <?php the_content(); ?>
  </div>
</div>
<a href="<?php echo esc_url($download_url); ?>" class="button">
  Download vCard
</a>
<?php
get_footer();
