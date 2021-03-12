<?php
/**
 * Filter view.
 *
 * @since 2.0.0
 */
?>
<div id="dashboard-filters" class="wp-filter wu-filter">

  <ul class="filter-links">

    <?php foreach ($views as $tab => $view) : ?>

      <li class="<?php echo $tab === $active_tab ? 'current' : ''; ?>">
        <a href="<?php echo esc_attr($view['url']); ?>"
          class="<?php echo $tab === $active_tab ? 'current wu-font-medium' : ''; ?> wu-loader">
          <?php echo $view['label']; ?>
        </a>
      </li>

    <?php endforeach; ?>

  </ul>

  <ul class="filter-links sm:wu-float-right sm:wu-w-1/2 lg:wu-w-1/4 wu--mx-2 wu-block sm:wu-inline-block">
    <li class="wu-w-full wu-relative">
      <span class="dashicons-wu-calendar wu-absolute wu-text-base wu-text-gray-600" style="top: 18px; left: 12px;"></span>
      <input
        id="wu-date-range"
        style="min-height: 28px;"
        class="wu-border-0 wu-border-l wu-border-gray-300 wu-bg-gray-100 wu-w-full wu-text-right wu-py-3 wu-outline-none wu-rounded-none"
        placeholder="Loading..."
      >
    </li>
  </ul>

  <ul class="wu-hidden md:wu-inline-block filter-links sm:wu-float-right md:wu-mr-6">

      <?php foreach ($preset_options as $slug => $preset) : ?>

        <?php 
        
          $link = add_query_arg(array(
            'start_date' => $preset['start_date'],
            'end_date'   => $preset['end_date'],
            'preset'     => $slug,
          ));

          $request_slug = wu_request('preset', 'none');
        
        ?>

        <li class="<?php echo $slug === $request_slug ? 'current' : ''; ?>">
          <a href="<?php echo esc_attr($link); ?>"
            class="<?php echo $slug === $request_slug ? 'current wu-font-medium' : ''; ?> wu-loader">
            <?php echo $preset['label']; ?>
          </a>
        </li>

    <?php endforeach; ?>

  </ul>

</div>
