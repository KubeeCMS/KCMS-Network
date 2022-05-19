<?php
/**
 * DNS table view.
 *
 * @since 2.0.0
 */
?>
<div id="wu-dns-table" class="wu-widget-list-table wu-advanced-filters wu--m-3 wu-mt-2 wu--mb-3 wu-border-0 wu-border-t wu-border-solid wu-border-gray-400">

  <table class="wp-list-table widefat fixed striped wu-border-t-0" v-cloak>

    <thead>
      <tr>
        <th class="wu-w-4/12"><?php _e('Host', 'wp-ultimo'); ?></th>
        <th class="wu-w-2/12"><?php _e('Type', 'wp-ultimo'); ?></th>
        <th class="wu-w-4/12"><?php _e('IP / Target', 'wp-ultimo'); ?></th>
        <th class="wu-w-2/12"><?php _e('TTL', 'wp-ultimo'); ?></th>
      </tr>
    </thead>

    <tbody v-if="loading">
      
      <tr>
      
        <td colspan="4">

          <?php _e('Loading DNS entries...', 'wp-ultimo'); ?>

        </td>
        
      </tr>
      
    </tbody>

    <tbody v-if="!loading && error">

      <tr>
      
        <td colspan="4">

          <div class="wu-mt-0 wu-p-4 wu-bg-red-100 wu-border wu-border-solid wu-border-red-200 wu-rounded-sm wu-text-red-500" v-html="error[0].message"></div>

        </td>
        
      </tr>

    </tbody>

    <tbody v-if="!loading && !error">

      <tr v-for="dns in results.entries">
        <td>{{ dns.host }}<span v-html="dns.tag" v-if="dns.tag"></span></td>
        <td>{{ dns.type }}</td>
        <td>{{ dns.data }}</td>
        <td>{{ dns.ttl }}</td>
      </tr>

      <tr v-for="dns in results.auth">
        <td>{{ dns.host }}<span v-html="dns.tag" v-if="dns.tag"></span></td>
        <td>{{ dns.type }}</td>
        <td>{{ dns.data }}</td>
        <td>{{ dns.ttl }}</td>
      </tr>

      <tr v-for="dns in results.additional">
        <td>{{ dns.host }}<span v-html="dns.tag" v-if="dns.tag"></span></td>
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
      error: null,
      results: {},
      loading: true,
    },
    updated() {
      this.$nextTick(function() {

        window.wu_initialize_tooltip();
        
      });
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

        } else {
          
          Vue.set(wu_dns_table, 'error', data.data);
          
        } // end if;

      },
    })

  });
})(jQuery);

</script>
