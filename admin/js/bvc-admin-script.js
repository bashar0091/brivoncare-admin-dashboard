jQuery(document).ready(function($) {
    var $adminWrapper = $("#adminmenuwrap");
    if ($adminWrapper.length) {
      var $customDiv = $("<div>", {
        id: "custom-admin-logo",
        css: {
          textAlign: "center",
          padding: "10px",
        },
      });

      var $a_tag = $("<a>", {
        href: window.location.origin + "/wp-admin/users.php?role=carer",
      });

      var $img = $("<img>", {
        src:
          window.location.origin +
          "/wp-content/plugins/brivoncare-admin-dashboard/admin/assets/img/logo.png",
        css: {
          width: "150px",
          height: "auto",
        },
      });

      var $wpIcon = $("<span>", {
        class: "dashicons dashicons-menu-alt3",
        id: "bvc-side-menu-togger",
        css: {
          fontSize: "35px",
          color: "#12133a",
        },
      });

       $a_tag.append($img);
       $customDiv.append($a_tag, $wpIcon);
       $adminWrapper.prepend($customDiv);
    }

    $("#adminmenumain .screen-reader-shortcut").remove();

    $("#bvc-side-menu-togger").on("click", function () {
      $("#adminmenumain").toggleClass("bvc-collapse");
      $("#wpcontent").toggleClass("bvc-collapse");
    });

});