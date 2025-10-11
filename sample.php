<?php

/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Coupon_Master_Theme
 */

get_header();

$postid = get_the_ID();
$image = get_the_post_thumbnail_url();
$title = get_the_title();
$date = get_the_date();
$time = get_the_time();
$content = get_the_content();
?>

<section class="py-20 bg-cover bg-no-repeat bg-center" style="background-image:linear-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.5)), url(<?php echo esc_url($image); ?>)">
    <div class="section_container">
        <h1 class="text-white text-center">
            <?php echo wp_kses_post($title); ?>
        </h1>
        <div class="flex items-center text-white justify-center gap-5">
            <div>
                <?php echo wp_kses_post($date); ?>
            </div>
            <div>
                <?php echo wp_kses_post($time); ?>
            </div>
            <div>
                No Comments
            </div>
        </div>

        <div class="social-share-buttons flex items-center justify-center gap-5">
            <!-- Facebook -->
            <a href="#" id="share-facebook" target="_blank" rel="noopener" aria-label="Share on Facebook">
                <svg width="24" height="24" fill="#1877F2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 12c0-5.522-4.477-10-10-10S2 6.478 2 12c0 5 3.657 9.128 8.438 9.878v-6.987h-2.54v-2.89h2.54V9.797c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.47h-1.26c-1.243 0-1.63.772-1.63 1.562v1.875h2.773l-.443 2.89h-2.33v6.987C18.343 21.128 22 17 22 12z" />
                </svg>
            </a>

            <!-- Twitter -->
            <a href="#" id="share-twitter" target="_blank" rel="noopener" aria-label="Share on Twitter">
                <svg width="24" height="24" fill="#1DA1F2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23 3a10.9 10.9 0 01-3.14.86A4.48 4.48 0 0022.43 1a9.06 9.06 0 01-2.88 1.1 4.48 4.48 0 00-7.62 4.1A12.8 12.8 0 013 2.15 4.48 4.48 0 004.8 9.7 4.41 4.41 0 012 9.2v.05a4.48 4.48 0 003.6 4.4 4.52 4.52 0 01-2 .07 4.48 4.48 0 004.18 3.12A9 9 0 012 19.54a12.78 12.78 0 006.92 2.03c8.3 0 12.85-6.87 12.85-12.84 0-.2 0-.4-.02-.6A9.18 9.18 0 0023 3z" />
                </svg>
            </a>

            <!-- Pinterest -->
            <a href="#" id="share-pinterest" target="_blank" rel="noopener" aria-label="Share on Pinterest">
                <svg width="24" height="24" fill="#BD081C" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C7.03 2 3 6.03 3 11c0 3.16 1.75 5.94 4.35 7.33-.06-.62-.12-1.56.02-2.24.13-.6.86-3.84.86-3.84s-.22-.43-.22-1.07c0-1 0-1.74 1.27-1.74 1.18 0 1.7.88 1.7 1.94 0 1.18-.75 2.94-1.14 4.57-.33 1.38.7 2.5 2.08 2.5 2.5 0 4.42-2.64 4.42-6.44 0-2.67-1.8-4.68-5.5-4.68-4.03 0-6.57 3-6.57 6.12 0 1.23.48 2.57 1.08 3.3a.43.43 0 01.1.41c-.1.45-.3 1.4-.34 1.6-.05.25-.18.3-.42.18-1.57-.73-2.54-3-2.54-4.84 0-3.94 2.86-7.56 8.24-7.56 4.32 0 7.66 3.12 7.66 7.3 0 4.35-2.74 7.85-6.56 7.85-1.28 0-2.5-.67-2.92-1.44l-.8 3.04c-.29 1.14-1.1 2.58-1.65 3.46 1.23.38 2.53.58 3.9.58 4.97 0 9-3.98 9-8.88 0-4.93-4.03-8.88-9-8.88z" />
                </svg>
            </a>

            <!-- Threads -->
            <a href="#" id="share-threads" target="_blank" rel="noopener" aria-label="Share on Threads">
                <svg width="24" height="24" fill="#000000" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <!-- Simple Thread icon example -->
                    <circle cx="12" cy="12" r="10" stroke="black" stroke-width="2" fill="none" />
                    <path d="M8 12h8M12 8v8" stroke="black" stroke-width="2" />
                </svg>
            </a>

            <!-- WhatsApp -->
            <a href="#" id="share-whatsapp" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
                <svg width="24" height="24" fill="#25D366" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20.52 3.48A11.88 11.88 0 0012 0a11.88 11.88 0 00-8.52 3.48 11.86 11.86 0 00-3.48 8.52c0 2.06.52 4 1.5 5.7L0 24l6.87-1.79a11.9 11.9 0 005.13 1.2 11.86 11.86 0 008.52-3.48 11.87 11.87 0 003.48-8.52 11.87 11.87 0 00-3.48-8.52zM12 21a9 9 0 01-5.17-1.58l-.37-.22-4.07 1.06 1.06-4.07-.22-.37A9 9 0 1121 12a8.9 8.9 0 01-9 9zm4.32-6.06l-1.21-.6a.62.62 0 00-.56 0l-.87.44a3.13 3.13 0 01-1.42.36 2.62 2.62 0 01-1.86-3.79l.12-.22a.62.62 0 00-.1-.67L9.3 8.67a.6.6 0 00-.58-.23l-1.58.28a.61.61 0 00-.52.64c.03.23.1.46.19.67a6.4 6.4 0 006.7 6.7c.22.1.43.16.64.19a.62.62 0 00.65-.52l.28-1.58a.61.61 0 00-.22-.6z" />
                </svg>
            </a>

            <!-- Email -->
            <a href="#" id="share-email" target="_blank" rel="noopener" aria-label="Share via Email">
                <svg width="24" height="24" fill="#7A7A7A" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 2v.01L12 13 4 6.01V6h16zM4 18v-9l8 6 8-6v9H4z" />
                </svg>
            </a>
        </div>

        <script>
            // Get current post/page URL and title
            var postUrl = encodeURIComponent(window.location.href);
            var postTitle = encodeURIComponent(document.title);

            // Set href for each share link dynamically
            document.getElementById('share-facebook').href = `https://www.facebook.com/sharer/sharer.php?u=${postUrl}`;
            document.getElementById('share-twitter').href = `https://twitter.com/intent/tweet?url=${postUrl}&text=${postTitle}`;
            document.getElementById('share-pinterest').href = `https://pinterest.com/pin/create/button/?url=${postUrl}&description=${postTitle}`;
            document.getElementById('share-threads').href = `https://www.threads.net/share?url=${postUrl}`;
            document.getElementById('share-whatsapp').href = `https://api.whatsapp.com/send?text=${postTitle}%20${postUrl}`;
            document.getElementById('share-email').href = `mailto:?subject=${postTitle}&body=Check%20this%20out:%20${postUrl}`;
        </script>

    </div>
</section>

<section>
    <div class="section_container">
        <div class="flex gap-5">
            <div class="shadow-md p-10 w-1/3">

                <div id="post-toc" class="toc-wrapper">
                    <div class="toc-header active" id="toggle-toc">
                        <span>Table of Contents</span>
                        <i class="fa-solid fa-chevron-down toc-icon"></i>
                    </div>
                    <div id="toc-content" class="toc-content" style="display: block;">
                        <ul>
                            <?php
                            $args = array(
                                'post_type'      => 'post',
                                'post__not_in' => array($postid),
                                'post_status'    => 'publish',
                            );

                            $query = new WP_Query($args);

                            if ($query->have_posts()) :
                                while ($query->have_posts()) : $query->the_post(); ?>
                                    <li>
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </li>
                            <?php endwhile;
                                wp_reset_postdata();
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>

                <style>
                    .toc-wrapper {
                        background: #fff;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        padding: 15px;
                        margin-bottom: 25px;
                        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                    }

                    .toc-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        cursor: pointer;
                    }

                    .toc-header span {
                        font-weight: 600;
                        font-size: 16px;
                    }

                    .toc-icon {
                        transition: transform 0.3s ease;
                        font-size: 14px;
                        color: #333;
                    }

                    /* Rotate the icon when active */
                    .toc-header.active .toc-icon {
                        transform: rotate(180deg);
                    }

                    .toc-content {
                        margin-top: 10px;
                        display: none;
                    }

                    .toc-content ul {
                        list-style: none;
                        padding-left: 10px;
                    }

                    .toc-content li {
                        margin-bottom: 6px;
                    }

                    .toc-content a {
                        text-decoration: none;
                        color: #333;
                    }

                    .toc-content a:hover {
                        color: #ff6600;
                    }
                </style>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const tocContainer = document.getElementById("toc-content");
                        const headings = document.querySelectorAll(".entry-content h2, .entry-content h3");
                        const list = document.createElement("ul");

                        headings.forEach(h => {
                            const id = h.id || h.textContent.trim().toLowerCase().replace(/\s+/g, '-');
                            h.id = id;
                            const li = document.createElement("li");
                            li.innerHTML = `<a href="#${id}">${h.textContent}</a>`;
                            list.appendChild(li);
                        });

                        tocContainer.appendChild(list);

                        const tocHeader = document.getElementById("toggle-toc");
                        const tocContent = document.getElementById("toc-content");

                        tocHeader.addEventListener("click", function() {
                            const isVisible = tocContent.style.display === "block";
                            tocContent.style.display = isVisible ? "none" : "block";
                            tocHeader.classList.toggle("active", !isVisible);
                        });
                    });
                </script>
            </div>

            <div class="shadow-md p-10 w-2/3">
                <div>
                    <?php echo wpautop(wp_kses_post($content)); ?>
                </div>

                <?php
                the_post_navigation(
                    array(
                        'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'coupon-master') . '</span> <span class="nav-title">%title</span>',
                        'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'coupon-master') . '</span> <span class="nav-title">%title</span>',
                    )
                );
                ?>
            </div>
        </div>
    </div>
</section>

<section class="mt-20">
    <div class="section_container">
        <?php
        $deal_args = array(
            'post_type'      => 'post',
            'post__not_in'   => array($postid),
            'order'          => 'rand',
            'post_status'    => 'publish',
        );
        $deal_query = new WP_Query($deal_args);

        ?>

        <?php if ($deal_query->have_posts()): ?>
            <div class="deal_slider swiper">
                <div class="swiper-wrapper">
                    <?php
                    while ($deal_query->have_posts()):
                        $deal_query->the_post();
                        $deal_image = get_the_post_thumbnail_url();
                        $deal_title = get_the_title();
                        $deal_description = get_the_content();
                        $deal_link = get_post_meta(get_the_ID(), 'couponmaster_deal_link', true);
                        $date = get_the_date();
                        $time = get_the_time();
                        $excerpt = get_the_excerpt();
                        $link = get_the_permalink();
                    ?>
                        <div class="swiper-slide">
                            <div>
                                <img src="<?php echo esc_url($deal_image); ?>" alt="">
                                <h3><?php echo wp_kses_post($deal_title); ?></h3>
                                <div class="flex items-center text-white justify-center gap-5">
                                    <div>
                                        <?php echo wp_kses_post($date); ?>
                                    </div>
                                    <div>
                                        <?php echo wp_kses_post($time); ?>
                                    </div>
                                </div>
                                <div>
                                    <?php echo wpautop(wp_kses_post($excerpt)); ?>
                                </div>
                                <a href="<?php esc_url($link); ?>">Continue Reading</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section>
    <div class="section_container">
        <?php
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>
    </div>
</section>

<?php
get_footer();
