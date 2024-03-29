<?php

namespace Gehog\StaticPages\Admin\Controller;

use Gehog\StaticPages\Common\Admin\ScreenController;

use function Gehog\StaticPages\pages;
use function Gehog\StaticPages\types;

/**
 * Class OptionsReadingController
 *
 * @package Gehog\StaticPages\Admin\Controller
 */
class OptionsReadingController extends ScreenController {
    /**
     * @inheritDoc
     */
    public function initialize() {
        $this->registerSections();
    }

    /**
     * Register settings form fields on reading section in the admin area.
     *
     * @return void
     */
    protected function registerSections() {
        \add_settings_section(
            'static_pages',
            __('Static pages', 'gehog-static-pages'),
            [$this, 'renderSettingSection'],
            'reading'
        );

        foreach (types()->getAll() as $type) {
            $this->registerSettingField($type);
        }
    }

    /**
     * Register setting form field for the specified static page type.
     *
     * @param \Gehog\StaticPages\StaticPage\StaticPageType $type
     * @return void
     */
    protected function registerSettingField($type) {
        $field_id = "static_page_{$type->name}";

        \add_settings_field(
          $field_id,
          $type->label,
          function () use ($type) {
              $this->renderSettingField($type);
          },
          'reading',
          'static_pages',
          ['label_for' => $field_id]
        );
    }

    /**
     * Renders setting section description.
     *
     * @return void
     */
    public function renderSettingSection() {
        if (types()->hasRegistration()) {
            printf(
              '<p>%s</p>',
              __(
                'List of registered static page types and their corresponding pages',
                'gehog-static-pages'
              )
            );
        }
        else {
            printf(
              '<p>%s</p>',
              __(
                'There are no registered static page types.',
                'gehog-static-pages'
              )
            );
        }
    }

    /**
     * Render certain setting field.
     *
     * @param \Gehog\StaticPages\StaticPage\StaticPageType $type
     * @return void
     */
    public function renderSettingField($type) {
        $static_page = pages()->findByType($type);
        $selected_page_id = 0;

        if (!is_null($static_page)) {
            $selected_page_id = intval($static_page->page_id);
        }

        $dropdown_args = [
          'id' => "static_page_{$type->name}",
          'name' => "gehog_static_pages[{$type->name}]",
          'show_option_none' => __('&mdash; Select &mdash;'),
          'option_none_value' => '0',
          'selected' => $selected_page_id,
          'exclude' => []
        ];

        $privacy_page_id = intval(get_option('wp_page_for_privacy_policy', 0));

        if (!empty($privacy_page_id)) {
            $dropdown_args['exclude'][] = $privacy_page_id;
        }

        if ('page' === get_option('show_on_front')) {
            foreach (['page_for_posts', 'page_on_front'] as $option) {
                $page_id = intval(get_option($option, 0));

                if (!empty($page_id)) {
                    $dropdown_args['exclude'][] = $page_id;
                }
            }
        }

        echo '<fieldset>';
        \wp_dropdown_pages($dropdown_args);

        if (!empty($type->description)) {
            printf('<p class="description">%s</p>', esc_html($type->description));
        }

        echo '</fieldset>';
    }
}
