# silverstripe-elemental-blog

## Purpose

The purpose of this module is to provide you with an Elemental Block/Element that can be used to replace the default
Blog template behaviour (EG: Use a Block to display a paginated list of Blog Posts, with support for pagination,
categorisation, and tags).

As there are many ways that you could be using Elemental, this module does **not** make assumptions about how you want
it to be applied. Examples are provided below.

## Installation

```
composer require chrispenny/silverstripe-elemental-blog
```

The Elemental module suggests that we override the `Layout` template, and use `$ElementalArea` to render the elements to
the page (in place of `$Content`). The following assumes that this is how you will be implementing your Blocks.

The Blog module itself uses the standard `Page.ss` template, but also provides it's own `Layout` template
(`SilverStripe\Blog\Model\Layout\Blog.ss`). So, out of the box, even if `Elemental` is available to your `Blog` page, no
Elements are going to be output in your template (as Silverstripe will first check for, and find, a `Layout\Blog`
template).

For us, using the Blog module, that means that we'll likely want to override the default Blog `Layout` template. The
idea being that we now want to render all aspects of the Blog through Blocks, rather than just through the `Layout`
template.

To do that, in the templates directory of your active theme (EG: `/themes/simple/templates/...`), add a new Layout
template matching the namespace of the Blog module's `Layout` template (`SilverStripe\Blog\Model\Layout\Blog.ss`).

So, your directories should look something like:

```
themes /
  simple /
    templates /
      SilverStripe /
        Blog /
          Model /
            Layout/
              Blog.ss
```

In your (new) `Blog.ss` file, you can now output `$ElementalArea` where you would like it.

EG `Blog.ss`:

```
<div class="content-container">
    $ElementalArea
</div>
```

## Usage

### Blog Overview Block

This is (likely) the main Block that you will be using. It will output a bunch of what was originally being output by
the Blog module's `Layout` template.

Including:

- Title (including Category/Archive/etc titles) (if `$ShowTitle` is ticked in the CMS for the Block)
- Blog Posts that were created under this particular Blog page (or simply **all** Blog Posts if this Block is not being
used on a Blog page)
- Pagination (if `$ShowPagination` is ticked in the CMS for the Block, and if the `Controller` for the page is able to
return a `PaginatedList`)
- Widgets (if `$ShowWidgets` is ticked in the CMS for the Block and if the Page is able to return a `WidgetArea`)

But **not** including:

- `$Content` (it is assumed, since you're building pages with Blocks, that your `$Content` will be coming from another
block)

You will likely want to override the very basic default template that I have provided, you can do so by overriding the
template found with the namespace `ChrisPenny\ElementalBlog\Model\BlogOverviewBlock.ss`.

**Please consider:** While the Overview Block does support you using it on other page types, it is primarily designed to
be used on Blog page types. This is because it is `Blog` and `BlogController` that provide the relevant info to this
Block. If you use this Block on other page types, then this Block's default behaviour is simply to return all Blog Posts
currently in the DB.

Please consider whether you want this Block to be available to other page types, and if you don't, you might want to
add this Block as a `disallowed` Element on your base Block page. EG:

```yaml
Page:
  disallowed_elements:
    - ChrisPenny\ElementalBlog\Model\BlogOverviewBlock
```

## Blog Pagination Block

Coming soon

## Blog Widgets Block

Coming soon
