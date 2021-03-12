<?php
/**
 * DNS table view.
 *
 * @since 2.0.0
 */
?>
<div id="wu-dns-table" class="wu-widget-list-table wu-advanced-filters wu--m-3 wu-mt-2 wu--mb-3 wu-border-0 wu-border-t wu-border-solid wu-border-gray-400">

  <div v-show="loading" class="wu-p-6 wu-block wu-text-center wu-bg-gray-100">

    <?php _e('Loading DNS entries...', 'wp-ultimo'); ?>

  </div>

  <table class="wp-list-table widefat fixed striped wu-border-t-0" v-cloak v-if="!loading">

    <thead>
      <tr>
        <th class=""><?php _e('Host', 'wp-ultimo'); ?></th>
        <th class=""><?php _e('Type', 'wp-ultimo'); ?></th>
        <th class="wu-w-1/2"><?php _e('IP / Target', 'wp-ultimo'); ?></th>
        <th class=""><?php _e('TTL', 'wp-ultimo'); ?></th>
      </tr>
    </thead>

    <tbody>

      <tr v-for="dns in results.entries">
        <td>{{ dns.host }}</td>
        <td>{{ dns.type }}</td>
        <td>{{ dns.data }}</td>
        <td>{{ dns.ttl }}</td>
      </tr>

      <tr v-for="dns in results.auth">
        <td>{{ dns.host }}</td>
        <td>{{ dns.type }}</td>
        <td>{{ dns.data }}</td>
        <td>{{ dns.ttl }}</td>
      </tr>

      <tr v-for="dns in results.additional">
        <td>{{ dns.host }}</td>
        <td>{{ dns.type }}</td>
        <td>{{ dns.data }}</td>
        <td>{{ dns.ttl }}</td>
      </tr>

      <tr>
        <td colspan="2"><?php _e('Your Network IP', 'wp-ultimo'); ?></td>
        <td colspan="2" class="wu-text-left">{{ results.network_ip }}</td>
      </tr>

    </tbody>

  </table>

</div>

<script>

(function($) {

  wu_dns_table = new Vue({
    el: '#wu-dns-table',
    data: {
      results: {},
      loading: true,
    }
  })

  $(document).ready(function() {

    $.ajax({
      url: ajaxurl,
      data: {
        action: 'wu_get_dns_records',
        domain: '<?php echo esc_js($domain->get_domain()); ?>',
      },
      success: function(data) {

        Vue.set(wu_dns_table, 'loading', false);

        if (data.success) {

          Vue.set(wu_dns_table, 'results', data.data);

        } // end if;

      },
    })

  });
})(jQuery);

</script>
