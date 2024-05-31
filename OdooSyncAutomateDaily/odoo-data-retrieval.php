    <?php 
    /*
    Plugin Name: Odoo Product Retrieval
    Description: Retrieves all products from Odoo.
    */

    add_action('odoo_sync_event', 'sync_odoo_data');

    function odoo_product_retrieval_shortcode() {
        ob_start();
        ?>
        <h2>Sản phẩm trong trang Odoo</h2>
        <div id="loading-icon" style="display: none;">Đang tải...</div>
        <div id="progress-bar-container" style="display: block;">
            <div id="progress-bar" style="width: 0%; height: 20px; background-color: #24A239;"></div>
        </div>
        <div id="odoo-products"></div>
        <button id="load-products">Đồng bộ dữ liệu Odoo</button>
        <script>
            let offset = 0;
            let autoLoad = true;
            let isLoading = false;

            function loadMoreProducts() {
                if (isLoading) return;
                isLoading = true;
                let loadingIcon = document.getElementById('loading-icon');
                loadingIcon.style.display = 'block';
                let progressBar = document.getElementById('progress-bar');
                progressBar.style.width = (parseInt(progressBar.style.width) + 10) + '%';
                if(progressBar.style.width == '100%'){
                    progressBar.style.width = '0%';
                }
                let xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('odoo-products').insertAdjacentHTML('beforeend', response.data);
                            offset += 10;
                            if (autoLoad) {
                                setTimeout(loadMoreProducts, 500);
                            }
                        } else {
                            alert('No more products to load.');
                            autoLoad = false;
                        }
                        // loadingIcon.style.display = 'none';
                        isLoading = false;
                    }
                };
                xhr.send('action=load_more_odoo_products&offset=' + offset);
            }

            document.getElementById('load-products').addEventListener('click', function() {
                autoLoad = true;
                loadMoreProducts();
                this.style.display = 'none';
            });
        </script>
        <?php
        wp_enqueue_style('odoo-data-style', site_url('/wp-content/plugins/odoo-data-retrieval/css/odoo-data-style.css'));

        return ob_get_clean();
    }
    add_shortcode('odoo_product_retrieval', 'odoo_product_retrieval_shortcode');

    function sync_odoo_data() {
        $offset = 0;
        while (true) {
            load_odoo_products($offset);
            $offset += 10;
            if (!has_more_data_from_odoo($offset)) {
                break;
            }
        }
    }


    //Automate sync to odoo daily
    function schedule_odoo_sync() {
        if (!wp_next_scheduled('odoo_sync_event')) {
            wp_schedule_event(time(), 'daily', 'odoo_sync_event');
        }
    }

    add_action('wp', 'schedule_odoo_sync');

    function has_more_data_from_odoo($offset) {
        $url = "https://edu-is336o12htcl-nhom2.odoo.com";
        $db = "edu-is336o12htcl-nhom2";
        $username = "21522454@gm.uit.edu.vn";
        $password = "erpclass";
    
        require_once(plugin_dir_path(__FILE__) . 'xmlrpc.inc');
    
        $common = new xmlrpc_client($url . "/xmlrpc/2/common");
        $common->request_charset_encoding = 'UTF-8';
        $msg = new xmlrpcmsg('authenticate', array(
            new xmlrpcval($db, "string"),
            new xmlrpcval($username, "string"),
            new xmlrpcval($password, "string"),
            new xmlrpcval(array(), "struct")
        ));
        $response = $common->send($msg);
    
        if (!$response->faultCode()) {
            $uid = $response->value()->scalarval();
    
            $client = new xmlrpc_client($url . "/xmlrpc/2/object");
            $client->request_charset_encoding = 'UTF-8';
    
            $msg = new xmlrpcmsg('execute_kw', array(
                new xmlrpcval($db, "string"),
                new xmlrpcval($uid, "int"),
                new xmlrpcval($password, "string"),
                new xmlrpcval("product.product", "string"),
                new xmlrpcval("search_count", "string"),
                new xmlrpcval(array(new xmlrpcval(array(), "array")), "array")
            ));
            $response = $client->send($msg);
    
            if (!$response->faultCode()) {
                $count = $response->value()->scalarval();
                return $count > $offset;
            } else {
                echo "Error fetching count of products: " . htmlspecialchars($response->faultString(), ENT_QUOTES, 'UTF-8');
                return false;
            }
        } else {
            echo "Error authenticating: " . htmlspecialchars($response->faultString(), ENT_QUOTES, 'UTF-8');
            return false;
        }
    }
    


    function load_odoo_products($offset) {
        $url = "https://edu-is336o12htcl-nhom2.odoo.com";
        $db = "edu-is336o12htcl-nhom2";
        $username = "21522454@gm.uit.edu.vn";
        $password = "erpclass";

        require_once(plugin_dir_path(__FILE__) . 'xmlrpc.inc');

        $common = new xmlrpc_client($url . "/xmlrpc/2/common");
        $common->request_charset_encoding = 'UTF-8';
        $msg = new xmlrpcmsg('authenticate', array(
            new xmlrpcval($db, "string"),
            new xmlrpcval($username, "string"),
            new xmlrpcval($password, "string"),
            new xmlrpcval(array(), "struct")
        ));
        $response = $common->send($msg);

        if (!$response->faultCode()) {
            $uid = $response->value()->scalarval();

            $client = new xmlrpc_client($url . "/xmlrpc/2/object");
            $client->request_charset_encoding = 'UTF-8';

            $msg = new xmlrpcmsg('execute_kw', array(
                new xmlrpcval($db, "string"),
                new xmlrpcval($uid, "int"),
                new xmlrpcval($password, "string"),
                new xmlrpcval("product.product", "string"),
                new xmlrpcval("search_read", "string"),
                new xmlrpcval(array(new xmlrpcval(array(), "array")), "array"),
                new xmlrpcval(array("fields" => new xmlrpcval(array(
                    new xmlrpcval("id", "string"),
                    new xmlrpcval("name", "string"),
                    new xmlrpcval("qty_available", "string"),
                    new xmlrpcval("list_price", "string"),
                    new xmlrpcval("categ_id", "string"),
                    new xmlrpcval("image_1920", "string")
                ), "array"),
                "offset" => new xmlrpcval($offset, "int"),
                "limit" => new xmlrpcval(10, "int")), "struct")
            ));
            $response = $client->send($msg);

            if (!$response->faultCode()) {
                $products = php_xmlrpc_decode($response->value(), 'utf-8');
                array_walk_recursive($products, function (&$item) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'auto');
                });

                foreach ($products as $product) {
                    $id = htmlspecialchars($product['id'] ?? '', ENT_QUOTES, 'UTF-8');
                    $name = htmlspecialchars($product['name'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                    $qty_available = htmlspecialchars($product['qty_available'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                    $list_price = htmlspecialchars($product['list_price'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                    $category = htmlspecialchars($product['categ_id'][1] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                    $image_url = isset($product['image_1920']) ? 'data:image/png;base64,'. $product['image_1920'] : '';

                    $product_data = array(
                        'id' => $id,
                        'name' => $name,
                        'qty_available' => $qty_available,
                        'list_price' => $list_price,
                        'category' => $category,
                        'image_url' => $image_url
                    );

                    if (!product_exists_in_woocommerce($id)) {
                        insert_product_to_woocommerce($product_data);
                    }

                    echo "<li>";
                    if ($image_url) {
                        echo "<img src='$image_url' alt='$name' style='max-width: 100px; max-height: 100px;' />";
                    }
                    echo "Product: $name, Price: $list_price, Category: $category";
                    echo "</li>";
                }
            } else {
                echo "Error fetching products: " . htmlspecialchars($response->faultString(), ENT_QUOTES, 'UTF-8');
            }
        } else {
            echo "Error authenticating: " . htmlspecialchars($response->faultString(), ENT_QUOTES, 'UTF-8');
        }
    }

    function product_exists_in_woocommerce($odoo_id) {
        $args = array(
            'post_type'      => 'product',
            'meta_query'     => array(
                array(
                    'key'       => 'odoo_id',
                    'value'     => $odoo_id,
                    'compare'   => '='
                )
            ),
            'post_status'    => 'any',
            'posts_per_page' => 1
        );

        $products = new WP_Query($args);
        return $products->have_posts();
    }

    function insert_product_to_woocommerce($product_data) {
        $url = "https://www.muchmade.id.vn/wp-json/wc/v3/products";
        $consumer_key = "ck_eecf7bf41059cfc863199c798aa6cc531f6520fc";
        $consumer_secret = "cs_c92f287761ba6c1cdc8997673f752abde3763f1d";

        $data = array(
            'name' => $product_data['name'],
            'type' => 'simple',
            'regular_price' => $product_data['list_price'],
            'description' => '',
            'short_description' => '',
            'stock_status' => 'instock',
            'categories' => array(
                array('id' => get_woocommerce_category_id($product_data['category']))
            )
        );

        if (!empty($product_data['image_url'])) {
            $image_id = upload_base64_image($product_data['image_url']);
            if ($image_id) {
                $data['images'] = array(
                    array(
                        'id' => $image_id
                    )
                );
            }
        }

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret),
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
            'timeout' => 60
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            if ($response_code == 201) {
                $product_id = json_decode($response_body)->id;
                update_post_meta($product_id, 'odoo_id', $product_data['id']);
                echo 'Product inserted successfully.';
            } else {
                echo 'Failed to insert product. Response code: ' . $response_code . ', Response body: ' . $response_body;
            }
        }
    }

    function upload_base64_image($base64_image) {
        $upload_dir = wp_upload_dir();
        $img = str_replace('data:image/png;base64,', '', $base64_image);
        $img = str_replace(' ', '+', $img);
        $decoded_image = base64_decode($img);
        $filename = 'odoo_image_' . time() . '.png';
        $file_path = $upload_dir['path'] . '/' . $filename;

        file_put_contents($file_path, $decoded_image);

        $resized_image = wp_get_image_editor($file_path);
        if (!is_wp_error($resized_image)) {
            $resized_image->resize(360, 360, true);
            $resized_image->save($file_path);
        }

        $filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file_path);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }

    function get_woocommerce_category_id($category_name) {
        $category = get_term_by('name', $category_name, 'product_cat');
        if ($category) {
            return $category->term_id;
        } else {
            $new_category = wp_insert_term($category_name, 'product_cat');
            if (!is_wp_error($new_category) && isset($new_category['term_id'])) {
                return $new_category['term_id'];
            } else {
                return 0;
            }
        }
    }

    function load_more_odoo_products() {
        if (isset($_POST['offset'])) {
            $offset = intval($_POST['offset']);
            ob_start();
            load_odoo_products($offset);
            $output = ob_get_clean();
            wp_send_json_success($output);
        } else {
            wp_send_json_error('Invalid offset');
        }
    }
    add_action('wp_ajax_load_more_odoo_products', 'load_more_odoo_products');
    add_action('wp_ajax_nopriv_load_more_odoo_products', 'load_more_odoo_products');
    ?>
