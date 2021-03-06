<?php
namespace SandersForPresidentLanding\Wordpress\Admin\Requests;

if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
use WP_List_Table;

class RequestTable extends WP_List_Table {
  private $service;

  public function __construct() {
    parent::__construct(array(
      'singular' => 'Request',
      'plural' => 'Requests',
      'ajax' => false
    ));
    $this->service = new RequestService();
  }

  public function get_columns() {
    return array(
      'cb' => '<input type="checkbox" />',
      'request_organization' => 'Organization',
      'request_organizer' => 'Organizer',
      'request_url' => 'URL',
      'request_date' => 'Date'
    );
  }

  public function get_views() {
    $views = array();
    $current = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 'pending';

    $class = $current == 'pending' ? 'class="current"' : '';
    $url = "?page={$_REQUEST['page']}";
    $count = wp_count_posts('request')->draft;
    $count = $count > 0 ? "<span class='count'>({$count})</span>" : "";
    $views['pending'] = "<a href='{$url}' {$class}>Pending {$count}</a>";

    $class = $current == 'approved' ? 'class="current"' : '';
    $url = "?page={$_REQUEST['page']}&post_status=approved";
    $count = wp_count_posts('request')->approved;
    $count = $count > 0 ? "<span class='count'>({$count})</span>" : "";
    $views['approved'] = "<a href='{$url}' {$class}>Approved {$count}</a>";

    $class = $current == 'rejected' ? 'class="current"' : '';
    $url = "?page={$_REQUEST['page']}&post_status=rejected";
    $count = wp_count_posts('request')->rejected;
    $count = $count > 0 ? "<span class='count'>({$count})</span>" : "";
    $views['rejected'] = "<a href='{$url}' {$class}>Rejected {$count}</a>";

    $class = $current == 'trash' ? 'class="current"' : '';
    $url = "?page={$_REQUEST['page']}&post_status=trash";
    $count = wp_count_posts('request')->trash;
    $count = $count > 0 ? "<span class='count'>({$count})</span>" : "";
    $views['trash'] = "<a href='{$url}' {$class}>Trash</a> {$count}";

    return $views;
  }

  public function column_cb($item) {
    return "<input type=\"checkbox\" name=\"request[]\" value=\"{$item['id']}\" />";
  }

  public function column_request_organization($item) {
    if (!$item['read']) {
      $title = "<strong>{$item['organization']}</strong>";
    } else {
      $title = $item['organization'];
    }
    $actions = array(
      'view' => "<a href=\"?page={$_REQUEST['page']}&action=view&post={$item['id']}\">View</a>"
    );
    return $title . $this->row_actions($actions, false);
  }

  public function column_request_organizer($item) {
    $name = $item['contact_name'];
    $email = "<a href=\"mailto:{$item['contact_email']}\">{$item['contact_email']}</a>";
    return $name . "<br/>" . $email;
  }

  public function column_request_url($item) {
    return $item['url'] . '.forberniesanders.com';
  }

  public function column_request_date($item) {
    return $item['date'];
  }

  public function get_sortable_columns() {
    return array(
      'message_title' => array('message_title', false)
    );
  }

  public function get_bulk_actions() {
    return array(
      'delete' => 'Delete'
    );
  }

  public function prepare_items() {
    $query = $this->service->getQueryRequests($_REQUEST['paged'], $_REQUEST['post_status']);
    $this->items = $query['posts'];
    $this->_column_headers = array($this->get_columns(), array(), array());
    $this->set_pagination_args(array(
      'total_items' => $query['count'],
      'per_page' => 10
    ));
  }
}
