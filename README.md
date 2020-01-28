# silverstripe-elemental-blog

## Purpose

The purpose of this module is to provide you with an Elemental Block that can be used to replace the default Blog
template behaviour (EG: Use a Block to display a paginated list of Blog Posts, with support for pagination,
categorisation, tags, and other widgets).

As there are many ways that you could be using Elemental, this module does **not** make assumptions about how you want
it to be applied. Examples are provided below.

## Installation

```
composer require chrispenny/silverstripe-elemental-blog
```

Depending on how you have applied `ElementalPageExtension`, you **may** need to add it to `Blog` and `BlogPost`:

```yaml
SilverStripe\Blog\Model\Blog:
  extensions:
    elemental: DNADesign\Elemental\Extensions\ElementalPageExtension

SilverStripe\Blog\Model\BlogPost:
  extensions:
    elemental: DNADesign\Elemental\Extensions\ElementalPageExtension
```

The Elemental module suggests that we override the `Layout` template, and use `$ElementalArea` to render the elements to
the page (in place of `$Content`). The following assumes that this is how you will be implementing your Blocks.

The Blog module itself uses the standard `Page.ss` template, but also provides its own `Layout` template
(`SilverStripe\Blog\Model\Layout\Blog.ss`). So, out of the box, even if `Elemental` is available to your `Blog` page, no
Elements are going to be output in your template (as Silverstripe will first check for, and will find, a `Layout\Blog`
template).

For us (who are wanting to switch to using Blocks to display our Blog) this means that we'll likely want to override the
default Blog `Layout` template. The idea being that we now want to render all aspects of the Blog through Blocks, rather
than just through the `Layout` template.

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

Your `Blog` page will now output Blocks, instead of its standard `Layout`.

## Usage

TL;DR: Have a look at `BlogOverviewBlock`. It has a tonne of configurable values, and should give you pretty good
control over how you want to use it.

`PaginationBlock` and `WidgetsBlock` both just extent the `OverviewBlock`. The only difference is that they have
different default configurations set (and their own, simplified template).

### Blog Overview Block

This is (likely) the main Block that you will be using. It will output a bunch of what was originally being output by
the Blog module's `Layout` template.

**Including:**

- Title (including Category/Archive/etc titles) (if `$ShowTitle` is ticked in the CMS for the Block)
- Blog Posts that were created under this particular Blog page (or simply **all** Blog Posts if this Block is not being
used on a Blog page)
- Pagination (if `$ShowPagination` is ticked in the CMS for the Block, and if the `Controller` for the page is able to
return a `PaginatedList`)
- Widgets (if `$ShowWidgets` is ticked in the CMS for the Block and if the Page is able to return a `WidgetArea`)

**But not including:**

- `$Content` (it is assumed, since you're building pages with Blocks, that your `$Content` will be coming from another
block)

You will likely want to override the very basic default template that I have provided, you can do so by overriding the
template found with the namespace `ChrisPenny\ElementalBlog\Model\BlogOverviewBlock.ss`.

**Please consider:** While the Overview Block does support you using it on other page types, it is primarily designed to
be used on Blog page types. This is because it is `Blog` and `BlogController` that provide the relevant info to this
Block. If you use this Block on other page types, then this Block's default behaviour is simply to return all Blog Posts
currently in the DB (though, you do have options to manipulate these through extension points).

Please consider whether you want this Block to be available to other page types, and if you don't, you might want to
add this Block as a `disallowed` Element on your other Block page. EG:

```yaml
Page:
  disallowed_elements:
    - ChrisPenny\ElementalBlog\Model\BlogOverviewBlock
```

## Blog Pagination Block

You might decide that you would like Pagination to be displayed quite separately to the Blog Overview Block. This can
be done using the `BlogPaginationBlock` (and probably disabling `ShowPagination` in `BlogOverviewBlock`).

By default, the Pagination Block relies on having a `Blog` page as it's parent, but you can also hook into the provided
extension points in order to update the `PaginatedList` by some other means.

**Please consider:** Like the Overview Block, please consider removing this Block from any/all Page that you do not
want it available on. EG:

```yaml
Page:
  disallowed_elements:
    - ChrisPenny\ElementalBlog\Model\BlogPaginationBlock
```

## Blog Widgets Block

You might decide that you would like Blog Widgets to be displayed quite separately to the Blog Overview Block. This can
be done using the `BlogWidgetsBlock` (and probably disabling `ShowWidgets` in `BlogOverviewBlock`).

By default, the Widgets Block relies on having a `Blog` page as it's parent, but you can also hook into the provided
extension points in order to update the `WidgetArea` by some other means.

**Please consider:** Like the Overview Block, please consider removing this Block from any/all Page that you do not
want it available on. EG:

```yaml
Page:
  disallowed_elements:
    - ChrisPenny\ElementalBlog\Model\BlogWidgetsBlock
```
