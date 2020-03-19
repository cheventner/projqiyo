<?php

/**
 * Themify_Updater class to handle various functionality of updater plugin.
 *
 * @since      1.1.4
 * @package    Themify_Updater
 * @author     Themify
 */

if ( !class_exists('Themify_Updater') ) :

    class Themify_Updater_Promotion
    {
        private $license = false;
        private $version = false;
        private $type;

        function __construct($license, $versions, $type)
        {
            $this->license = $license;
            $this->version = $versions;
            $this->type = $type;
        }

        public function load(){
            wp_enqueue_script( 'wp-util' );
            $promo = $this->get_downloadable_products( $this->type, true, true );
            if ( isset($promo['installed']) ) $promo['installed'] = $this->add_versions( $promo['installed'] );
            wp_localize_script('themify-upgrader', 'themify_promotion', $promo );
            wp_localize_script('themify-upgrader', 'themify_updater_promo', array(
                'text' => array( 'latest_version' =>  __( 'Latest version', 'themify-updater' ))
            ) );

            if ( !defined('THEMIFY_UPDATER_MENU_PAGE') ) define('THEMIFY_UPDATER_MENU_PAGE', 1);
            require ( THEMIFY_UPDATER_DIR_PATH.'/templates/promotion.php' );
        }

        /**
         * Products which can be downloaded with current licence.
         *
         * @param string $type
         * @param bool $filter
         * @param bool $promo_name
         * @return array
         */
        function get_downloadable_products($type = 'theme', $filter = true, $promo_name = false) {
            $temp = $ret = array();
            $free = array();

            if ( is_object($this->version) ) {
                $query = "//version[@type='" . $type . "']";
                $elements = $this->version->run_query($query);
                if ($elements->length) {
                    foreach ($elements as $field) {
                        $temp[] = $field->getAttribute('name');

                        $r = $field->getAttribute('free');
                        if ( ! empty($r) ) {
                            $free[] = $field->getAttribute('name');
                        }

                    }
                }
            }

            $products = $this->get_products();
            $ret = array_intersect($temp, $products);
            $ret = array_unique(array_merge($ret, $free));
            $buy = array_diff($temp, $ret);

            if ($filter) {
                $installed = array();
                switch ($type) {
                    case 'plugin':
                        if ( ! function_exists( 'get_plugins' ) ) {
                            require_once ABSPATH . 'wp-admin/includes/plugin.php';
                        }
                        $installed_plugins = get_plugins();

                        if( !empty( $installed_plugins ) ) {
                            foreach ( $installed_plugins as $key => $plugin ) {
                                if (dirname( $key ) === 'themify-icons') $installed[] =  dirname( $key ).'-plugin';
                                else $installed[] =  dirname( $key );
                            }
                        }
                        break;
                    default:
                        $installed_themes = Themify_Updater_utils::wp_get_themes();
                        if( !empty( $installed_themes ) ) {
                            $installed = array_keys($installed_themes);
                        }
                }
                $temp = array();
                $temp['installed'] = array_intersect($ret, $installed);
                $temp['install'] = array_diff( $ret, $temp['installed']);
                $temp['install'] = $this->add_nonce($temp['install'], 'install', $type);
                $temp['installed'] = $this->add_nonce($temp['installed'], 'upgrade', $type);
                $temp['buy'] = $buy;
                $ret = $temp;
            } else {
                $ret = $this->add_nonce($ret, 'install', $type);
            }

            if ($promo_name) {
                if ($filter) {
                    foreach ($ret as $key => $arr) {
                        foreach ($arr as $key2 => $value) {
                            $ret[$key][$key2] = array('name' => $value, 'promo' => $this->version->has_attribute($value, 'promo_name', true));
                        }
                    }
                } else {
                    foreach ($ret as $key => $value) {
                        $ret[$key] = array('name' => $value, 'promo' => $this->version->has_attribute($value, 'promo_name', true));
                    }
                }
            }
            return $ret;
        }

        /**
         * @return array
         */
        private function get_products() {
            if ( is_object($this->license) )
                return $this->license->get_products();
            else
                return array();
        }

        /**
         * @param $promo
         * @return mixed
         */
        private function add_versions($promo){
            foreach ( $promo as $key => $product ){
                $promo[$key]['version'] = $this->version->remote_version( $product['name'] );
            }

            return $promo;
        }

        /**
         * @param array $inputs
         * @param string $nonce_type
         * @param string $type
         * @return array
         */
        private function add_nonce($inputs = array(), $nonce_type = 'install', $type = 'theme') {
            $tempA = array();
            foreach ($inputs as $input) {
                $key = '';
                if ( $nonce_type == 'install' ) {
                    $key = wp_create_nonce( "install-". $type ."_". str_replace("-plugin", "", $input) );
                } elseif ( $nonce_type == 'upgrade' ) {
                    $key = wp_create_nonce( "upgrade-". $type ."_". str_replace("-plugin", "", $input) );
                }
                $tempA[$key] = $input;
            }
            return $tempA;
        }
    }
endif;