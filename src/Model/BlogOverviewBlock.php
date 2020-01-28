<?php

namespace ChrisPenny\ElementalBlog\Model;

use DNADesign\Elemental\Models\BaseElement;
use Exception;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Blog\Model\BlogController;
use SilverStripe\Blog\Model\BlogPost;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Widgets\Extensions\WidgetPageExtension;
use SilverStripe\Widgets\Model\WidgetArea;

/**
 * Class BlogOverviewBlock
 *
 * @package ChrisPenny\ElementalBlog\Model
 * @property int $ShowPagination
 * @property int $ShowWidgets
 */
class BlogOverviewBlock extends BaseElement
{
    /**
     * @var array
     */
    private static $db = [
        'ShowPagination' => 'Boolean',
        'ShowWidgets' => 'Boolean',
    ];

    /**
     * @var string
     */
    private static $icon = 'font-icon-p-articles';

    /**
     * @var string
     */
    private static $table_name = 'BlogOverviewBlock';

    /**
     * @var string
     */
    private static $singular_name = 'Blog overview block';

    /**
     * @var string
     */
    private static $plural_name = 'Blog overview blocks';

    /**
     * @var string
     */
    private static $description = 'Block displaying Blog Posts with pagination';

    /**
     * We use this default_title for the Block name in the CMS. Feel free to update it via config
     *
     * @var string
     */
    private static $default_title = 'Blog Overview';

    /**
     * OotB there is really no reason for a content author to enter this Block to make any edits, so, by default, we'll
     * just set a generic default Title. You can disable this via config
     *
     * @var bool
     */
    private static $set_default_title = true;

    /**
     * By default, we show the "Show Pagination" field in the CMS (since it is part of the default supported features).
     * You may, however, prefer that content authors display pagination for the Blog using the specific
     * `PaginationBlock`, and if that's the case, you'll likely want to set this value to `0` via config, as it will
     * no longer be relevant for your content authors
     *
     * @var int
     */
    private static $show_pagination_field = 1;

    /**
     * Default value for ShowPagination. If set, this block will also output the pagination for your Blog. You can
     * update this value via config
     *
     * @var int
     */
    private static $pagination_field_default = 1;

    /**
     * Since the Widgets module is an addon for the Blog module (and not out of the box), we've hidden the CMS field by
     * default (using this config). You can update this via config. If set to `1`, we will display the CMS field for
     * your users to use
     *
     * @var int
     */
    private static $show_widgets_field = 0;

    /**
     * Since the Widgets module is an addon for the Blog module (and not out of the box), we've set the default for this
     * field to be `0`. You can update this via config
     *
     * @var int
     */
    private static $widgets_field_default = 0;

    /**
     * This can be updated via config if (for whatever reason) you do not wish to show this message field in the CMS
     *
     * @var int
     */
    private static $show_info_message_field = 1;

    /**
     * Default value used for the message field in the CMS. You can update this via config
     *
     * @var string
     */
    private static $info_message_field_default = 'This block will automatically display Blog Posts and pagination';

    /**
     * Cached value for BlogPosts from the Blog page
     *
     * @var DataList|BlogPost[]|null
     */
    private $blogPosts;

    /**
     * Cached value for the PaginatedList from BlogController
     *
     * @var PaginatedList|null
     */
    private $paginatedList;

    /**
     * Cached value for WidgetArea from Blog page
     *
     * @var WidgetArea|null
     */
    private $widgetArea;

    /**
     * Cached value for our CacheKey. It's not all that cheap to generate it, so, we should only do it once per
     * request
     *
     * @var string|null
     */
    private $cacheKey;

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getType(): string
    {
        return static::config()->get('default_title');
    }

    /**
     * @codeCoverageIgnore
     * @return FieldList
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        // Removing scaffold fields so that they can be added more explicitly (and allowing for update via extension
        // points)
        $fields->removeByName([
            'ShowPagination',
            'ShowWidgets',
        ]);

        if (static::config()->get('show_pagination_field')) {
            $showPaginationField = CheckboxField::create('ShowPagination');

            $this->invokeWithExtensions('updateShowPaginationField', $showPaginationField);

            $fields->addFieldToTab(
                'Root.Main',
                $showPaginationField
            );
        }

        if (static::config()->get('show_widgets_field')) {
            $showWidgetsField = CheckboxField::create('ShowWidgets');

            $this->invokeWithExtensions('updateShowWidgetsField', $showWidgetsField);

            $fields->addFieldToTab(
                'Root.Main',
                $showWidgetsField
            );
        }

        if (static::config()->get('show_info_message_field')) {
            $messageField = LiteralField::create(
                'BlockInfoMessage',
                sprintf(
                    '<p style="text-align:center">%s</p>',
                    static::config()->get('info_message_field_default')
                )
            );

            $this->invokeWithExtensions('updateMessageField', $messageField);

            $fields->addFieldToTab(
                'Root.Main',
                $messageField
            );
        }

        return $fields;
    }

    public function populateDefaults(): void
    {
        parent::populateDefaults();

        if (static::config()->get('set_default_title')) {
            $this->Title = static::config()->get('default_title');
        }

        $this->ShowPagination = static::config()->get('pagination_field_default');
        $this->ShowWidgets = static::config()->get('widgets_field_default');
    }

    /**
     * @return DataList
     * @throws ValidationException
     */
    public function getBlogPosts(): ?DataList
    {
        if ($this->blogPosts !== null) {
            return $this->blogPosts;
        }

        /** @var Blog $page */
        $page = $this->getPage();

        // Ideally, we want to fetch the BlogPosts that were specifically posted under this Blog page, but, if this
        // Block is not being used on the Blog page, then that is not possible, and so instead, we'll just return all
        // Blog Posts in the DB. You can then update either of these lists (maybe with additional filters/limits/etc) by
        // using the `updateBlogPosts` extension point below
        if ($page instanceof Blog) {
            $blogPosts = $page->getBlogPosts();
        } else {
            $blogPosts = BlogPost::get();
        }

        $this->invokeWithExtensions('updateBlogPosts', $blogPosts);

        $this->blogPosts = $blogPosts;

        return $this->blogPosts;
    }

    /**
     * @return PaginatedList|null
     * @throws ValidationException
     */
    public function getPaginatedList(): ?PaginatedList
    {
        if ($this->paginatedList !== null) {
            return $this->paginatedList;
        }

        /** @var BlogController $controller */
        $controller = Controller::curr();

        // Ideally, we want to fetch the PaginatedList from the BlogController, but, if this Block is not being used on
        // a Blog page, then that will not be (immediately) possible. You have two options:
        // 1) You can use the `updatePaginatedList` extension point to apply your filters/limits there
        // 2) You can implement a method `getBlogPostPaginatedList` on your Controller, and provide it with the
        //    appropriate data
        if ($controller instanceof BlogController) {
            $paginatedList = $controller->PaginatedList();
        } else {
            if ($controller->hasMethod('getBlogPostPaginatedList')) {
                $paginatedList = $controller->getBlogPostPaginatedList();
            } else {
                $paginatedList = PaginatedList::create($this->getBlogPosts());
            }
        }

        $this->invokeWithExtensions('updatePaginatedList', $paginatedList);

        $this->paginatedList = $paginatedList;

        return $this->paginatedList;
    }

    /**
     * @return WidgetArea|null
     * @throws ValidationException
     */
    public function SideBarView(): ?WidgetArea
    {
        if ($this->widgetArea !== null) {
            return $this->widgetArea;
        }

        $page = $this->getPage();

        // We can't get widgets for the Page if the Page type doesn't have the Widget extension
        if (!$page->hasExtension(WidgetPageExtension::class)) {
            // You get one last chance to return a WidgetArea through some other means
            if ($page->hasMethod('SideBarView')) {
                $widgetArea = $page->SideBarView();

                if (!$widgetArea instanceof WidgetArea) {
                    throw new Exception('SideBarView expected to return class type WidgetArea');
                }

                $this->widgetArea = $widgetArea;

                return $this->widgetArea;
            }

            return null;
        }

        // If the Page is inheriting it's SideBar, then we should grab it from the Parent
        if ($page->InheritSideBar
            && ($parent = $page->getParent())
            && $parent->hasMethod('SideBarView')
        ) {
            $widgetArea = $parent->SideBarView();

            $this->invokeWithExtensions('updateWidgetArea', $widgetArea);

            $this->widgetArea = $widgetArea;

            return $this->widgetArea;
        }

        // Otherwise, let's attempt to fetch the SideBar from this current Page
        if ($page->SideBar()->exists()) {
            $widgetArea = $page->SideBar();

            $this->invokeWithExtensions('updateWidgetArea', $widgetArea);

            $this->widgetArea = $widgetArea;

            return $this->widgetArea;
        }

        return null;
    }

    /**
     * If you're using partial cache, then use can use this as the cache key for the Block
     *
     * @return string|null
     * @throws ValidationException
     */
    public function getCacheKey(): ?string
    {
        if ($this->cacheKey !== null) {
            return $this->cacheKey;
        }

        $cacheKey = implode(
            '-',
            [
                static::class,
                $this->ID,
                $this->LastEdited,
                $this->getBlogPosts()->count(),
                $this->getBlogPosts()->max('LastEdited')
            ]
        );

        $this->invokeWithExtensions('updateCacheKey', $cacheKey);

        $this->cacheKey = $cacheKey;

        return $this->cacheKey;
    }
}
