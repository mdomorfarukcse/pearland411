<!-- Search Page Content -->

<style>
    /* ======= Root Variables ======= */
    :root {
        --primary-color: #180f5e;
        --accent-color: #7bb73f;
        --text-dark: #333;
        --text-light: #666;
        --bg-light: #f8f9fa;
        --border-color: #e0e0e0;
    }

    /* ======= Search Header ======= */
    .search-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        padding: 80px 0 40px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .search-header::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .search-header .content {
        position: relative;
        z-index: 2;
    }

    .search-form {
        background: white;
        border-radius: 50px;
        padding: 6px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        max-width: 600px;
        margin: 30px auto 0;
        display: flex;
        align-items: center;
    }

    .search-input {
        border: none;
        border-radius: 50px;
        padding: 16px 24px;
        font-size: 16px;
        outline: none;
        flex: 1;
        background: transparent;
    }

    .search-btn {
        background: var(--accent-color);
        border: none;
        border-radius: 50px;
        padding: 12px 24px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .search-btn:hover {
        background: var(--primary-color);
        transform: scale(1.05);
    }

    /* ======= Search Results ======= */
    .search-results-container {
        padding: 60px 0;
        margin-bottom: 70px;
    }

    .result-stats {
        background: white;
        padding: 20px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border-left: 4px solid var(--accent-color);
    }

    .result-stats h5 {
        color: var(--primary-color);
        font-weight: 600;
        margin: 0;
    }

    .result-section {
        margin-bottom: 50px;
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding: 20px 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--accent-color);
    }

    .section-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 20px;
        color: white;
        background: var(--accent-color);
    }

    .section-title h4 {
        color: var(--primary-color);
        font-weight: 600;
        margin: 0;
    }

    .section-title p {
        color: var(--text-light);
        margin: 0;
        font-size: 14px;
    }

    /* ======= Grid Layout for Cards ======= */
    .results-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .results-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .results-grid {
            grid-template-columns: 1fr;
        }
    }

    .result-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-color: var(--accent-color);
    }

    .result-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .result-title a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .result-title a:hover {
        color: var(--accent-color);
    }

    .result-description {
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 15px;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 96px;
    }

    .result-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 14px;
        color: var(--text-light);
        flex-wrap: wrap;
        margin-top: auto;
    }

    .result-badge {
        background: var(--accent-color);
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }

    .result-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ======= No Results ======= */
    .no-results {
        text-align: center;
        padding: 80px 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .no-results-icon {
        font-size: 80px;
        color: var(--border-color);
        margin-bottom: 20px;
    }

    .no-results h4 {
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .no-results p {
        color: var(--text-light);
        font-size: 16px;
    }

    /* ======= Sidebar ======= */
    .sidebar-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border: 1px solid var(--border-color);
    }

    .sidebar-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-title i {
        color: var(--accent-color);
    }

    .popular-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .popular-list li {
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .popular-list li:last-child {
        border-bottom: none;
    }

    .popular-list li:hover {
        padding-left: 10px;
        background: var(--bg-light);
        border-radius: 8px;
    }

    .popular-list a {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .popular-list a:hover {
        color: var(--accent-color);
    }

    .news-item {
        padding: 15px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .news-item:last-child {
        border-bottom: none;
    }

    .news-item h6 {
        margin-bottom: 8px;
    }

    .news-item h6 a {
        color: var(--text-dark);
        text-decoration: none;
        line-height: 1.4;
    }

    .news-item h6 a:hover {
        color: var(--accent-color);
    }

    .news-item small {
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ======= Responsive ======= */
    @media (max-width: 768px) {
        .search-header {
            padding: 60px 0 30px;
        }

        .search-results-container {
            padding: 30px 0;
        }

        .section-header {
            flex-direction: column;
            text-align: center;
        }

        .section-icon {
            margin-bottom: 15px;
            margin-right: 0;
        }

        .result-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>

<!-- Search Header -->
<section class="search-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center content">
                <h1 class="mb-3">
                    <?php if (!empty($search_query)): ?>
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                    <?php else: ?>
                        Search Pearland411
                    <?php endif; ?>
                </h1>
                <p class="mb-0">Find businesses, news, events, and more in Pearland</p>

                <!-- Search Form -->
                <form method="GET" action="search" class="search-form">
                    <input type="text" name="q" class="search-input"
                        placeholder="Search businesses, news, events..."
                        value="<?php echo isset($search_query) ? htmlspecialchars($search_query) : ''; ?>"
                        autocomplete="off">
                    <button type="submit" class="search-btn">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="search-results-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($search_query)): ?>
                    <!-- Result Stats -->
                    <div class="result-stats">
                        <h5>
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                            </svg>
                            Found <?php echo isset($search_results['total_count']) ? $search_results['total_count'] : 0; ?> results
                        </h5>
                    </div>

                    <?php if (isset($search_results['total_count']) && $search_results['total_count'] > 0): ?>

                        <!-- Business Listings -->
                        <?php if (isset($search_results['listings']) && !empty($search_results['listings'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022zM6 8.694 1 10.36V15h5V8.694zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5V15z" />
                                            <path d="M2 11h1v1H2v-1zm2 0h1v1H4v-1zm-2 2h1v1H2v-1zm2 0h1v1H4v-1zm4-4h1v1H8V9zm2 0h1v1h-1V9zm-2 2h1v1H8v-1zm2 0h1v1h-1v-1zm2-2h1v1h-1V9zm0 2h1v1h-1v-1zM8 7h1v1H8V7zm2 0h1v1h-1V7zm2 0h1v1h-1V7zM8 5h1v1H8V5zm2 0h1v1h-1V5zm2 0h1v1h-1V5zm0-2h1v1h-1V3z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Business Listings (<?php echo count($search_results['listings']); ?>)</h4>
                                        <p>Local businesses and services</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['listings'] as $listing): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>listing-details/<?php echo isset($listing->listing_slug) ? $listing->listing_slug : $listing->listing_id; ?>">
                                                    <?php echo htmlspecialchars($listing->listing_name); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($listing->listing_description) ? strip_tags($listing->listing_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Business</span>
                                                <?php if (isset($listing->category_name) && !empty($listing->category_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586V2zm3.5 4a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z" />
                                                            <path d="M1.293 7.793A1 1 0 0 1 1 7.086V2a2 2 0 0 0-2 2v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0L8 15.707l-6.707-7.914z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($listing->category_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (isset($listing->owner_name) && !empty($listing->owner_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($listing->owner_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- News -->
                        <?php if (isset($search_results['news']) && !empty($search_results['news'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm4-2.5a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H9.5a.5.5 0 0 1-.5-.5zM9 8a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H9.5A.5.5 0 0 1 9 8zm1 2.5a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H10.5a.5.5 0 0 1-.5-.5z" />
                                            <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2ZM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4Z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>News (<?php echo count($search_results['news']); ?>)</h4>
                                        <p>Latest news and updates</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['news'] as $news): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>news_details/<?php echo isset($news->news_slug) ? $news->news_slug : $news->news_id; ?>">
                                                    <?php echo htmlspecialchars($news->news_title); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($news->news_description) ? strip_tags($news->news_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">News</span>
                                                <?php if (isset($news->author_name) && !empty($news->author_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($news->author_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Events -->
                        <?php if (isset($search_results['events']) && !empty($search_results['events'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Events (<?php echo count($search_results['events']); ?>)</h4>
                                        <p>Upcoming events and activities</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['events'] as $event): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>event-details/<?php echo isset($event->event_slug) ? $event->event_slug : $event->event_id; ?>">
                                                    <?php echo htmlspecialchars(isset($event->event_name) ? $event->event_name : (isset($event->event_title) ? $event->event_title : 'Event')); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($event->event_description) ? strip_tags($event->event_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Event</span>
                                                <?php if (isset($event->organizer_name) && !empty($event->organizer_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($event->organizer_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Blogs -->
                        <?php if (isset($search_results['blogs']) && !empty($search_results['blogs'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z" />
                                            <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-.33 2.653 2.653 0 0 0 .78-.579.725.725 0 0 0 .15-.798c-.081-.281-.408-.503-.934-.503-.526 0-.934.197-.934.503 0 .085.017.169.049.248.417 1.005.417 2.34-.49 3.64-.979 1.403-2.686 1.383-2.686 1.383.016-.875.752-1.519 1.582-1.519.434 0 .83.129 1.143.334.312.206.539.486.659.848.04.118.06.249.06.384.001.507-.166.986-.504 1.329-.842.85-2.206.283-2.206.283zm8.101.573c.016-.875.752-1.519 1.582-1.519.434 0 .83.129 1.143.334.312.206.539.486.659.848.04.118.06.249.06.384.001.507-.166.986-.504 1.329-.842.85-2.206.283-2.206.283a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-.33 2.653 2.653 0 0 0 .78-.579.725.725 0 0 0 .15-.798c-.081-.281-.408-.503-.934-.503-.526 0-.934.197-.934.503 0 .085.017.169.049.248.417 1.005.417 2.34-.49 3.64-.979 1.403-2.686 1.383-2.686 1.383z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Blogs (<?php echo count($search_results['blogs']); ?>)</h4>
                                        <p>Blog posts and articles</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['blogs'] as $blog): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>blogs-details/<?php echo isset($blog->blog_slug) ? $blog->blog_slug : $blog->blog_id; ?>">
                                                    <?php echo htmlspecialchars(isset($blog->blog_name) ? $blog->blog_name : (isset($blog->blog_title) ? $blog->blog_title : 'Blog Post')); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($blog->blog_description) ? strip_tags($blog->blog_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Blog</span>
                                                <?php if (isset($blog->author_name) && !empty($blog->author_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($blog->author_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Products -->
                        <?php if (isset($search_results['products']) && !empty($search_results['products'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Products (<?php echo count($search_results['products']); ?>)</h4>
                                        <p>Products for sale</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['products'] as $product): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>products-details/<?php echo isset($product->product_slug) ? $product->product_slug : $product->product_id; ?>">
                                                    <?php echo htmlspecialchars(isset($product->product_name) ? $product->product_name : (isset($product->product_title) ? $product->product_title : 'Product')); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($product->product_description) ? strip_tags($product->product_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Product</span>
                                                <?php if (isset($product->seller_name) && !empty($product->seller_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($product->seller_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (isset($product->product_price) && !empty($product->product_price)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z" />
                                                        </svg>
                                                        $<?php echo number_format($product->product_price, 2); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- House -->
                        <?php if (isset($search_results['house']) && !empty($search_results['house'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Houses for Sale (<?php echo count($search_results['house']); ?>)</h4>
                                        <p>Real estate properties for sale</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['house'] as $house): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>house-details/<?php echo isset($house->house_slug) ? $house->house_slug : $house->house_id; ?>">
                                                    <?php echo htmlspecialchars($house->house_title); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($house->house_description) ? strip_tags($house->house_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">House</span>
                                                <?php if (isset($house->house_price) && !empty($house->house_price)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z" />
                                                        </svg>
                                                        $<?php echo number_format($house->house_price, 2); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (isset($house->house_address) && !empty($house->house_address)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars(substr($house->house_address, 0, 30)) . (strlen($house->house_address) > 30 ? '...' : ''); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Rental -->
                        <?php if (isset($search_results['rental']) && !empty($search_results['rental'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5Z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Rentals (<?php echo count($search_results['rental']); ?>)</h4>
                                        <p>Properties for rent</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['rental'] as $rental): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>rental-details/<?php echo isset($rental->rental_slug) ? $rental->rental_slug : $rental->rental_id; ?>">
                                                    <?php echo htmlspecialchars($rental->rental_title); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($rental->rental_description) ? strip_tags($rental->rental_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Rental</span>
                                                <?php if (isset($rental->rental_price) && !empty($rental->rental_price)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z" />
                                                        </svg>
                                                        <?php
                                                        // Price may be a plain number ("3350") or a free-form
                                                        // string/range ("$890 – $1,500"); only format the former.
                                                        $rp = $rental->rental_price;
                                                        echo is_numeric($rp) ? '$' . number_format($rp, 2) : e($rp);
                                                        ?>/month
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (isset($rental->rental_address) && !empty($rental->rental_address)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars(substr($rental->rental_address, 0, 30)) . (strlen($rental->rental_address) > 30 ? '...' : ''); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Classifieds -->
                        <?php if (isset($search_results['classifieds']) && !empty($search_results['classifieds'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M14 4.5V9h-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v7H2V2a2 2 0 0 1 2-2h5.5L14 4.5zM1.6 11.85H0v3.999h.791v-1.342h.803c.287 0 .531-.057.732-.173.203-.117.358-.275.463-.474a1.42 1.42 0 0 0 .161-.677c0-.25-.053-.476-.158-.677a1.176 1.176 0 0 0-.46-.477c-.2-.12-.443-.179-.732-.179Zm.545 1.333a.795.795 0 0 1-.085.38.574.574 0 0 1-.238.241.794.794 0 0 1-.375.082H.788V12.48h.66c.218 0 .389.06.512.181.123.122.185.296.185.522Zm1.217-1.333v3.999h1.46c.401 0 .734-.08.998-.237a1.45 1.45 0 0 0 .595-.689c.13-.3.196-.662.196-1.084 0-.42-.065-.778-.196-1.075a1.426 1.426 0 0 0-.589-.68c-.264-.156-.599-.234-1.005-.234H3.362Zm.791.645h.563c.248 0 .45.05.606.152a.89.89 0 0 1 .354.454c.079.201.118.452.118.753a2.3 2.3 0 0 1-.068.592 1.14 1.14 0 0 1-.196.422.8.8 0 0 1-.334.252 1.298 1.298 0 0 1-.483.082h-.56v-2.707Zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638H7.896Z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Classifieds (<?php echo count($search_results['classifieds']); ?>)</h4>
                                        <p>Classified advertisements</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['classifieds'] as $classified): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>classifieds-details/<?php echo isset($classified->classified_slug) ? $classified->classified_slug : $classified->classified_id; ?>">
                                                    <?php echo htmlspecialchars(isset($classified->classifieds_title) ? $classified->classifieds_title : (isset($classified->classified_name) ? $classified->classified_name : 'Classified Ad')); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($classified->classifieds_description) ? strip_tags($classified->classifieds_description) : (isset($classified->classified_description) ? strip_tags($classified->classified_description) : '');
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Classified</span>
                                                <?php if (isset($classified->classified_price) && !empty($classified->classified_price)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z" />
                                                        </svg>
                                                        $<?php echo number_format($classified->classified_price, 2); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Videos -->
                        <?php if (isset($search_results['videos']) && !empty($search_results['videos'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814l-3.5-2.5z" />
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm15 0a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                                        </svg>
                                    </div>
                                    <div class="section-title">
                                        <h4>Videos (<?php echo count($search_results['videos']); ?>)</h4>
                                        <p>Video content</p>
                                    </div>
                                </div>

                                <div class="results-grid">
                                    <?php foreach ($search_results['videos'] as $video): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url() ?>video_details/<?php echo isset($video->video_slug) ? $video->video_slug : $video->video_id; ?>">
                                                    <?php echo htmlspecialchars(isset($video->video_title) ? $video->video_title : (isset($video->video_name) ? $video->video_name : 'Video')); ?>
                                                </a>
                                            </h5>
                                            <?php
                                            $description = isset($video->video_description) ? strip_tags($video->video_description) : '';
                                            if (!empty($description)): ?>
                                                <div class="result-description">
                                                    <?php
                                                    if (strlen($description) > 150) {
                                                        echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($description);
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="result-meta">
                                                <span class="result-badge">Video</span>
                                                <?php if (isset($video->uploader_name) && !empty($video->uploader_name)): ?>
                                                    <span class="result-meta-item">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($video->uploader_name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Services -->
                        <?php if (!empty($search_results['services'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-title">
                                        <h4>Services (<?php echo count($search_results['services']); ?>)</h4>
                                        <p>IT &amp; web services we offer</p>
                                    </div>
                                </div>
                                <div class="results-grid">
                                    <?php foreach ($search_results['services'] as $svc): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url('services/' . rawurlencode($svc->slug)) ?>">
                                                    <?php echo htmlspecialchars($svc->name); ?>
                                                </a>
                                            </h5>
                                            <p class="result-description">
                                                <?php echo htmlspecialchars($svc->short_description); ?>
                                            </p>
                                            <div class="result-meta">
                                                <span class="result-badge">Service</span>
                                                <?php if (!empty($svc->category)): ?>
                                                    <span class="result-meta-item">
                                                        <?php echo htmlspecialchars($svc->category); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Coupons -->
                        <?php if (!empty($search_results['coupons'])): ?>
                            <div class="result-section">
                                <div class="section-header">
                                    <div class="section-title">
                                        <h4>Coupons (<?php echo count($search_results['coupons']); ?>)</h4>
                                        <p>Current deals from local businesses</p>
                                    </div>
                                </div>
                                <div class="results-grid">
                                    <?php foreach ($search_results['coupons'] as $coup): ?>
                                        <div class="result-card">
                                            <h5 class="result-title">
                                                <a href="<?= base_url('coupons/' . (int) $coup->coupon_id) ?>">
                                                    <?php echo htmlspecialchars($coup->coupon_name); ?>
                                                </a>
                                            </h5>
                                            <p class="result-description">
                                                Code: <code><?php echo htmlspecialchars($coup->coupon_code); ?></code>
                                            </p>
                                            <div class="result-meta">
                                                <span class="result-badge">Coupon</span>
                                                <span class="result-meta-item">
                                                    Through <?php echo htmlspecialchars(date('M j, Y', strtotime($coup->coupon_end_date))); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- No Results -->
                        <div class="no-results">
                            <div class="no-results-icon">
                                <svg width="80" height="80" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                                </svg>
                            </div>
                            <h4>No results found for "<?php echo htmlspecialchars($search_query); ?>"</h4>
                            <p>Try different keywords or browse our categories.</p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Empty Search State -->
                    <div class="no-results">
                        <div class="no-results-icon">
                            <svg width="80" height="80" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                            </svg>
                        </div>
                        <h4>Start Your Search</h4>
                        <p>Enter keywords above to search across all content on Pearland411.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Search Tips -->
                <div class="sidebar-card">
                    <h6 class="sidebar-title">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 10px;">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zM7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
                        </svg>
                        Search Tips
                    </h6>
                    <ul class="popular-list">
                        <li>
                            <a href="#">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                                </svg>
                                Use specific keywords for better results
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                                </svg>
                                Try different spelling variations
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                                </svg>
                                Search by location or category
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Popular Categories -->
                <?php if (isset($categories) && !empty($categories)): ?>
                    <div class="sidebar-card">
                        <h6 class="sidebar-title">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 10px;">
                                <path d="M2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586V2zm3.5 4a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z" />
                                <path d="M1.293 7.793A1 1 0 0 1 1 7.086V2a2 2 0 0 0-2 2v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0L8 15.707l-6.707-7.914z" />
                            </svg>
                            Popular Categories
                        </h6>
                        <ul class="popular-list">
                            <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                                <li>
                                    <?php
                                    // Use slug if available, otherwise generate from name, fallback to ID
                                    $category_url = '';
                                    if (isset($category->category_slug) && !empty($category->category_slug)) {
                                        $category_url = $category->category_slug;
                                    } elseif (!empty($category->category_name)) {
                                        $category_url = strtolower(str_replace(' ', '-', $category->category_name));
                                    } else {
                                        $category_url = $category->category_id;
                                    }
                                    ?>
                                    <a href="<?= base_url() ?>all_listing/<?php echo $category_url; ?>">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                                        </svg>
                                        <?php echo htmlspecialchars($category->category_name); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Latest News -->
                <?php if (isset($latest_news) && !empty($latest_news)): ?>
                    <div class="sidebar-card">
                        <h6 class="sidebar-title">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 10px;">
                                <path d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm4-2.5a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H9.5a.5.5 0 0 1-.5-.5zM9 8a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H9.5A.5.5 0 0 1 9 8zm1 2.5a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H10.5a.5.5 0 0 1-.5-.5z" />
                                <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2ZM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4Z" />
                            </svg>
                            Latest News
                        </h6>
                        <?php foreach (array_slice($latest_news, 0, 5) as $news): ?>
                            <div class="news-item">
                                <h6>
                                    <a href="<?= base_url() ?>news_details/<?php echo isset($news->news_slug) ? $news->news_slug : $news->news_id; ?>">
                                        <?php echo htmlspecialchars($news->news_title); ?>
                                    </a>
                                </h6>
                                <small>
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 5px;">
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                                    </svg>
                                    <?php echo isset($news->created_at) ? date('M d, Y', strtotime($news->created_at)) : 'Recent'; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        // Search form submit
        $('.search-input').on('keypress', function(e) {
            if (e.which === 13) {
                $(this).closest('form').submit();
            }
        });

        // Smooth scroll to results
        if (window.location.search.includes('q=')) {
            $('html, body').animate({
                scrollTop: $('.search-results-container').offset().top - 100
            }, 800);
        }
    });
</script>