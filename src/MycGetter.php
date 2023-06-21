<?php

namespace RadishLab\MycGetter;

class MycGetter
{
    protected $post;
    protected $args;
    protected $presets;
    protected $defaultPreset = [
        'fields'        => ['title', 'url', 'image'],
        'image_size'    => 'large',
        'image_type'    => 'html',
        'acf'           => false,
        'taxonomies'    => false,
    ];

    /**
     * Load all presets and set of the selected preset
     *
     * @param string|array $args The name of a preset or an array of settings
     */
    public function __construct($args = 'default')
    {
        $this->loadPresets();
        $this->parseArgs($args);
    }

    /**
     * Create an array with all existing presets
     */
    protected function loadPresets()
    {
        $defaultPreset = $this->defaultPreset;
        $presets['default'] = $defaultPreset;

        $presets = apply_filters('myc_getter_presets', $presets);

        $this->presets = array_map(function ($preset) use ($defaultPreset) {
            return array_merge($defaultPreset, $preset);
        }, $presets);
    }

    /**
     * Create the array with all the selected settings
     *
     * @param string|array $args The name of a preset or an array of settings
     */
    protected function parseArgs($args)
    {
        if (!is_string($args) && !is_array($args)) {
            throw new \Exception('$args must be a string or an array.');
        }

        if (is_string($args)) {
            $this->args = $this->getPreset($args);
        }

        if (is_array($args)) {
            $this->args = array_merge($this->defaultPreset, $args);
        }
    }

    /**
     * Return the settings of a specific preset
     *
     * @param string $args The name of the preset
     * @return array
     */
    protected function getPreset($args)
    {
        $presetName = esc_attr($args);
        if (empty($this->presets[$presetName])) {
            throw new \Exception("The preset {$presetName} does not exist.");
        }

        return $this->presets[$presetName];
    }

    /**
     * Escape the content of a post
     *
     * @param int|object $post The post ID or WP_Post object
     * @return array
     */
    public function escapedContent($post)
    {
        $data = [];
        $this->extractPost($post);

        $fieldsData = new MycGetterPost($this->post, $this->args);
        $data = $fieldsData->escapePostFields();

        $taxonomyData = new MycGetterTaxonomy($this->post, $this->args);
        $data['taxonomy'] = $taxonomyData->escapePostTaxonomies();

        $acfData = new MycGetterAcf($this->post, $this->args);
        $data['acf'] = $acfData->escapePostAcf();

        return $data;
    }

    /**
     * Get the post content
     *
     * @param int|object $post The post ID or WP_Post object
     */
    protected function extractPost($post)
    {
        $postObject = $post;

        if (is_int($post)) {
            $post_id = absint($post);
            $postObject = get_post($post_id);
        }

        if (!is_object($postObject)) {
            throw new \Exception('$post must be an integer or an object.');
        }

        $this->post = $postObject;
    }
}
