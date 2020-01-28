<?php

namespace ChrisPenny\ElementalBlog\Model;

/**
 * A Block that has the intention of being used to display just the Widgets that have been specified for this Page.
 *
 * The reason I have made it extend BlogOverviewBlock, however, is because you *may* decide to use this differently. If
 * we simply extend BlogOverviewBlock, then you do also get access to BlogPosts/PaginatedList/etc.
 *
 * All this class has done (really) is set different config/default values.
 *
 * Class BlogWidgetsBlock
 *
 * @package ChrisPenny\ElementalBlog\Model
 */
class BlogWidgetsBlock extends BlogOverviewBlock
{
    /**
     * @var string
     */
    private static $icon = 'font-icon-block-layout';

    /**
     * @var string
     */
    private static $table_name = 'BlogWidgetsBlock';

    /**
     * @var string
     */
    private static $singular_name = 'Blog widgets block';

    /**
     * @var string
     */
    private static $plural_name = 'Blog widgets blocks';

    /**
     * @var string
     */
    private static $description = 'Block displaying Blog Widgets';

    /**
     * We use this default_title for the Block name in the CMS. Feel free to update it via config
     *
     * @var string
     */
    private static $default_title = 'Blog Widgets';

    /**
     * OotB there is really no reason for a content author to enter this Block to make any edits, so, by default, we'll
     * just set a generic default Title. You can disable this via config
     *
     * @var bool
     */
    private static $set_default_title = true;

    /**
     * This Block is not intended to display pagination, so, hide this field by default
     *
     * @var int
     */
    private static $show_pagination_field = 0;

    /**
     * This Block is not intended to display pagination, so, set this to `0` by default
     *
     * @var int
     */
    private static $pagination_field_default = 0;

    /**
     * This is the Widgets Block, we assume that we always want Widgets to be displayed (since that's all it will
     * display). So, no point in showing the CMS field to allow folks to toggle this on/off
     *
     * @var int
     */
    private static $show_widgets_field = 0;

    /**
     * @var int
     */
    private static $widgets_field_default = 1;

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
    private static $info_message_field_default = 'This block will automatically display Blog Widgets';
}
