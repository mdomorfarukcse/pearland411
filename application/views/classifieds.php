<style>
    .pagination-container .pagination {
        justify-content: center;
    }
</style>

<!-- START -->
<section class=" blog-head" style="margin-top: 0px !important; ">
    <div class="container">
        <div class="blog-head-inn">
            <h1>Classifieds</h1>
            <?php if (isset($search_query) && !empty($search_query)): ?>
                <p>Search results for "<strong><?= htmlspecialchars($search_query); ?></strong>" - <?= count($all_classifieds); ?> classifieds found</p>
            <?php else: ?>
                <p>Browse the latest classifieds and offers.</p>
            <?php endif; ?>
        </div>
        <div class="ban-search">
            <form action="<?= base_url('classifieds'); ?>" method="GET">
                <ul>
                    <li class="sr-sea">
                        <input type="text" name="q" id="classifieds-search" class="autocomplete" placeholder="Search classifieds..." value="<?= isset($search_query) ? htmlspecialchars($search_query) : '' ?>">
                        <button type="submit" style="display: none;"></button>
                    </li>
                </ul>
            </form>
        </div>
    </div>
</section>
<!--END-->

<style>
    .classifieds-showcase {
        padding: 60px 20px;
        /* background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); */
        min-height: 400px;
        margin-bottom: 100px !important;
    }

    .classifieds-wrapper {
        max-width: 1280px;
        margin: 0 auto;
    }

    .classifieds-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 28px;
        padding: 0;
        list-style: none;
        margin: 0;
    }

    .classified-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .classified-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .classified-image-wrapper {
        position: relative;
        width: 100%;
        height: 220px;
        overflow: hidden;
        background: #f0f0f0;
    }

    .classified-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .classified-card:hover .classified-image {
        transform: scale(1.08);
    }

    .classified-content {
        padding: 24px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .classified-date {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .classified-title {
        font-size: 22px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 12px 0;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .classified-description {
        font-size: 15px;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex-grow: 1;
    }

    .classified-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .classified-category {
        font-size: 13px;
        font-weight: 600;
        color: #10b981;
        display: flex;
        align-items: center;
        gap: 6px;
    }


    .classified-author {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    .classified-link {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        text-indent: -9999px;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 100px 30px;
    }

    .empty-state-content {
        max-width: 500px;
        margin: 0 auto;
        background: white;
        padding: 50px 40px;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .empty-state-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 16px 0;
    }

    .empty-state-text {
        font-size: 16px;
        color: #6b7280;
        line-height: 1.7;
        margin: 0;
    }

    .empty-state-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .empty-state-link:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .classifieds-showcase {
            padding: 40px 16px;
        }

        .classifieds-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .classified-title {
            font-size: 20px;
        }
    }
</style>

<section class="classifieds-showcase">
    <div class="classifieds-wrapper">
        <ul class="classifieds-grid">
            <?php if (!empty($all_classifieds)) : ?>
                <?php foreach ($all_classifieds as $classifieds) : ?>
                    <li>
                        <div class="classified-card">
                            <div class="classified-image-wrapper">
                                <img class="classified-image"
                                    src="<?= !empty($classifieds->classifieds_image) ? base_url($classifieds->classifieds_image) : base_url('assets/images/no_photo.jpg') ?>"
                                    alt="<?= htmlspecialchars($classifieds->classifieds_title) ?>">
                            </div>
                            <div class="classified-content">
                                <div class="classified-date">
                                    <?= date('d M, Y', strtotime($classifieds->created_at)) ?>
                                </div>
                                <h2 class="classified-title">
                                    <?= htmlspecialchars($classifieds->classifieds_title) ?>
                                </h2>
                                <p class="classified-description">
                                    <?= htmlspecialchars($classifieds->classifieds_description) ?>
                                </p>
                                <div class="classified-meta">
                                    <div class="classified-category">
                                        <?= htmlspecialchars($classifieds->category_name) ?>
                                    </div>
                                    <!-- IF AUTHOR EXISTS -->
                                    <?php if (!empty($classifieds->name)): ?>
                                        <div class="classified-author">
                                            By <?= htmlspecialchars($classifieds->name) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= base_url('classifieds-details/' . $classifieds->classifieds_slug); ?>"
                                class="classified-link">View Details</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li class="empty-state">
                    <div class="empty-state-content">
                        <?php if (isset($search_query) && !empty($search_query)): ?>
                            <h3 class="empty-state-title">No Results Found</h3>
                            <p class="empty-state-text">
                                We couldn't find any classifieds matching "<strong><?= htmlspecialchars($search_query); ?></strong>".
                                Try different keywords or
                                <a href="<?= base_url('classifieds'); ?>" class="empty-state-link">browse all classifieds</a>.
                            </p>
                        <?php else: ?>
                            <h3 class="empty-state-title">No Classifieds Available</h3>
                            <p class="empty-state-text">
                                There are no classifieds to display at the moment. Check back soon for new listings!
                            </p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</section>
<!--END-->

<?php if (!empty($pagination_links)): ?>
    <div class="pagination-wrapper" style="margin: 40px 0; text-align: center; clear: both; width: 100%; padding-bottom: 100px;">
        <div class="pagination-container" style="display: inline-block; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 100%;">
            <div class="pagination-info" style="margin-bottom: 15px; color: #718096; font-size: 14px;">
                <i class="material-icons" style="vertical-align: middle; font-size: 18px; margin-right: 5px;">pages</i>
                Navigate through news
            </div>
            <div class="pagination-links">
                <?= $pagination_links; ?>
            </div>
        </div>
    </div>
    <style>
        .pagination-wrapper .pagination {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            list-style: none;
            padding: 0;
        }

        .pagination-wrapper .pagination li {
            list-style: none;
            margin: 0;
            display: inline-block;
        }

        .pagination-wrapper .pagination li a,
        .pagination-wrapper .pagination li span {
            display: inline-block;
            padding: 8px 14px;
            margin: 0;
            text-decoration: none;
            background: #fff;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            min-width: 44px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .pagination-wrapper .pagination li a:hover {
            background: #7bb73f;
            color: white;
            border-color: #7bb73f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(123, 183, 63, 0.3);
        }

        .pagination-wrapper .pagination li.active a,
        .pagination-wrapper .pagination li.page-item.active a {
            background: linear-gradient(135deg, #7bb73f 0%, #6ba532 100%);
            color: white;
            border-color: #7bb73f;
            box-shadow: 0 4px 12px rgba(123, 183, 63, 0.4);
            font-weight: 600;
        }

        .pagination-wrapper .pagination li:first-child a,
        .pagination-wrapper .pagination li:last-child a {
            background: #f7fafc;
            color: #2d3748;
            font-weight: 600;
            border-color: #cbd5e0;
        }

        .pagination-wrapper .pagination li:first-child a:hover,
        .pagination-wrapper .pagination li:last-child a:hover {
            background: #180f5e;
            color: white;
            border-color: #180f5e;
        }

        @media (max-width: 768px) {

            .pagination-wrapper .pagination li a,
            .pagination-wrapper .pagination li span {
                padding: 8px 12px;
                font-size: 13px;
                min-width: 38px;
            }

            .pagination-container {
                padding: 15px !important;
            }

            .pagination-info {
                font-size: 13px !important;
            }
        }

        @media (max-width: 480px) {
            .pagination-wrapper .pagination {
                gap: 4px;
            }

            .pagination-wrapper .pagination li a,
            .pagination-wrapper .pagination li span {
                padding: 6px 10px;
                font-size: 12px;
                min-width: 32px;
            }
        }

        .pagination-wrapper .pagination li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .pagination-wrapper .pagination li a:hover::before {
            left: 100%;
        }
    </style>
<?php endif; ?>

<!--END-->