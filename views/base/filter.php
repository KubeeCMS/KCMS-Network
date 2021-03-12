<?php
/**
 * Filter view.
 *
 * @since 2.0.0
 */
?>
<div 
    id="<?php echo esc_attr($filters_el_id); ?>" 
    class="wp-filter wu-filter <?php echo !$table->has_items() ? 'wu-opacity-50 wu-pointer-events-none' : ''; ?>"
>

    <?php if (!empty($views)) : ?>

    <ul class="filter-links">

        <?php foreach ($views as $view_slug => $view) : ?>

            <li
                class="<?php echo wu_request($view['field'], 'all') == $view_slug ? esc_attr('current') : ''; ?>"
                :class="view && view === '<?php echo esc_attr($view_slug); ?>' ? 'current wu-font-medium' : ''"
            >
                <a
                    v-on:click.prevent="set_view('<?php echo esc_attr($view['field']); ?>', '<?php echo esc_attr($view_slug); ?>')"
                    href="<?php echo esc_attr($view['url']); ?>"
                    class="<?php echo wu_request($view['field'], 'all') == $view_slug ? esc_attr('current wu-font-medium') : ''; ?>"
                    :class="view && view === '<?php echo esc_attr($view_slug); ?>' ? 'current wu-font-medium' : ''"
                >

                    <?php echo esc_attr($view['label']); ?>

                </a>
            </li>

        <?php endforeach; ?>

    </ul>

    <?php endif; ?>

    <?php if (false) : ?>

        <button
            v-show="!open"
            v-on:click.prevent="open_filters"
            type="button"
            class="button drawer-toggle"
            v-bind:aria-expanded="open ? 'true' : 'false'"
        >
            <?php _e('Advanced Filters', 'wp-ultimo'); ?>
        </button>

        <div class="wu-py-3 wu-px-2 wu-inline-block wu-uppercase wu-font-semibold wu-text-gray-600 wu-text-xs" v-show="open" v-cloak>
            <?php _e('Advanced Filters', 'wp-ultimo'); ?>
        </div>

        <button
            v-show="open"
            v-on:click.prevent="close_filters"
            type="button"
            class="button drawer-toggle"
        >
            <?php _e('Close', 'wp-ultimo'); ?>
        </button>

    <?php endif; ?>

    <form class="search-form">

        <?php if (isset($has_search) && $has_search) : ?>

            <label class="screen-reader-text" for="wp-filter-search-input">
                <?php echo esc_html($search_label); ?>
            </label>

            <input
                name='s' id="s"
                value="<?php echo esc_attr(isset($_REQUEST['s']) ? $_REQUEST['s'] : ''); ?>"
                placeholder="<?php echo esc_attr($search_label); ?>"
                type="search"
                aria-describedby="live-search-desc"
                id="wp-filter-search-input"
                class="wp-filter-search"
            >

        <?php endif; ?>

    </form>

    <?php if (isset($has_view_switch) && $has_view_switch) : ?>

        <?php $table->view_switcher($table->current_mode); ?>

    <?php endif; ?>

    <div v-cloak v-show="false" class="wu-hidden">

        <div class="wu-clear-both"></div>

        <div class="wu-mb-3">

            <div
                v-for="(filter, index) in filters"
                class="wu-row wu-flex wu-p-4 wu-mt-0 wu-my-3 wu-bg-gray-100 wu-rounded wu-border wu-border-solid wu-border-gray-200"
            >

                <div class="wu-w-1/12 wu-mx-2 wu-text-right wu-self-center">

                    <span
                        class="wu-uppercase wu-font-semibold wu-text-gray-600 wu-text-xs"
                        v-if="index === 0"
                    >
                        <?php _e('Where', 'wp-ultimo'); ?>
                    </span>

                    <select
                        class="form-control wu-w-full"
                        v-if="index === 1"
                        v-model="relation"
                    >
                        <option value="and"><?php _e('and', 'wp-ultimo'); ?></option>
                        <option value="or"><?php _e('or', 'wp-ultimo'); ?></option>
                    </select>

                    <span
                        class="wu-uppercase wu-font-semibold wu-text-gray-600 wu-text-xs"
                        v-if="index > 1"
                    >
                        <span v-show="relation === 'and'"><?php _e('and', 'wp-ultimo'); ?></span>
                        <span v-show="relation === 'or'"><?php _e('or', 'wp-ultimo'); ?></span>
                    </span>

                </div>

                <div class="wu-w-2/12">

                    <select class="form-control wu-w-full" v-model="filter.field">

                         <option
                            v-for="available_filter in available_filters"
                            :value="available_filter.field"
                            v-html="available_filter.label"
                        >
                            &nbsp;
                        </option>

                    </select>

                </div>

                <div class="wu-w-2/12 wu-mx-2">

                    <select class="form-control wu-w-full" v-if="get_filter_type(filter.field) == 'bool'" v-model="filter.value">
                        <option value="1"><?php _e('is true.', 'wp-ultimo'); ?></option>
                        <option value="0"><?php _e('is false.', 'wp-ultimo'); ?></option>
                    </select>

                    <select class="form-control wu-w-full" v-if="get_filter_type(filter.field) == 'text'" v-bind:value="get_filter_rule(filter.field)">
                        <option value="is"><?php _e('is', 'wp-ultimo'); ?></option>
                        <option value="is_not"><?php _e('is not', 'wp-ultimo'); ?></option>
                        <option value="contains"><?php _e('contains', 'wp-ultimo'); ?></option>
                        <option value="does_not_contain"><?php _e('does not contain', 'wp-ultimo'); ?></option>
                        <option value="starts_with"><?php _e('starts with', 'wp-ultimo'); ?></option>
                        <option value="ends_with"><?php _e('ends with', 'wp-ultimo'); ?></option>
                        <option value="is_empty"><?php _e('is empty.', 'wp-ultimo'); ?></option>
                        <option value="is_not_empty"><?php _e('is not empty.', 'wp-ultimo'); ?></option>
                    </select>

                    <select class="form-control wu-w-full" v-if="get_filter_type(filter.field) == 'date'" v-bind:value="get_filter_rule(filter.field)">
                        <option value="before"><?php _e('is before', 'wp-ultimo'); ?></option>
                        <option value="after"><?php _e('is after', 'wp-ultimo'); ?></option>
                    </select>

                </div>

                <div class="wu-w-2/12">

                    <input
                        type="text"
                        class="form-control wu-w-full"
                        placeholder="<?php esc_attr_e('Value', 'wp-ultimo'); ?>"
                        v-if="_.contains(['text', 'date'], get_filter_type(filter.field)) && !_.contains(['is_empty', 'is_not_empty'], filter.rule)"
                        v-model="filter.value"
                    />

                </div>

                <div class="wu-w-2/12 wu-self-center wu-mx-3">

                    <a
                        href="#"
                        v-on:click.prevent="remove_filter(index)"
                        class="button"
                        v-show="index > 0"
                    >
                        <?php _e('Remove Filter', 'wp-ultimo'); ?>
                    </a>

                </div>

                <div class="wu-w-3/12 wu-self-center">

                    <a
                        href="#"
                        v-on:click.prevent="add_new_filter"
                        class="button button-primary wu-float-right"
                        v-show="index === filters.length - 1"
                    >
                        <?php _e('Add new Filter', 'wp-ultimo'); ?>
                    </a>

                </div>

            </div>

        </div>

    </div>
</div>
