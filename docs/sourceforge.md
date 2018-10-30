#  SourceForge Repository Configuration

Traduttore supports both private and public source code repositories hosted on [SourceForge.com](https://sourceforge.net).

SourceForge simultaneously supports Git, Mercurial, and Subversion to access their repositories, and you can choose whichever system you want when setting up a project in Traduttore. 

## Repository Access

Traduttore connects to SourceForge via either HTTPS or SSH to fetch a project's repository. If you're projects are not public, you need to make sure that the server has access to them by providing an SSH key.

You can learn more about this at [SourceForge's SSH Key Overview](https://sourceforge.net/p/forge/documentation/SSH%20Keys/)

## Webhooks

To enable automatic string extraction from your SourceForge projects, you need to create a new webhook for each of them. Webhooks are available for Git, SVN, and Mercurial repositories.

1. In your repository, expand the Admin section in the left menu. Then, click on the "Webhooks" link.
2. Under the `repo-push` label, click on "Create".
3. Set `https://<url-to-your-glotpress-site>.com/wp-json/traduttore/v1/incoming-webhook` as the payload URL.
5. Enter the secret token defined in `TRADUTTORE_SOURCEFORGE_SYNC_SECRET` or leave empty to generate one automatically (make sure to update the constant accordingly).

Now, every time you push changes to SourceForge, Traduttore will get notified and then attempts to update the project's translatable strings automatically.

**Note:** The `TRADUTTORE_SOURCEFORGE_SYNC_SECRET` constant needs to be defined in your `wp-config.php` file to enable webhooks. Use the secret from step 5 for this.

Check out the [Configuration](configuration.md) section for a list of possible constants.

## Self-managed SourceForge

Some people prefer to install SourceForge on their own system instead of using [SourceForge.com](https://sourceforge.net).

Unfortunately, Traduttore does not yet automatically recognize self-managed repositories, which means there is some manual configuration involved.

Let's say your SourceForge instance is available via `gitlab.example.com`. To tell Traduttore this should be treated as such, you can hook into the `traduttore.repository` filter to do so. Here's an example:

```php
class MySelfhostedSourceForgeRepository extends \Required\Traduttore\Repository\SourceForge {
	/**
	 * Indicates whether a SourceForge repository is publicly accessible or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool Whether the repository is publicly accessible.
	 */
	public function is_public() : bool {
		$response = wp_remote_head( 'https://gitlab.example.com/api/v4/projects/' . rawurlencode( $this->get_name() ) );

		return 200 === wp_remote_retrieve_response_code( $response );
	}
}

/**
 * Filters the repository information Traduttore uses for self-managed SourceForge repositories.
 *
 * @param \Required\Traduttore\Repository|null $repository Repository instance.
 * @param \Required\Traduttore\Project         $project    Project information.
 * @return \Required\Traduttore\Repository|null Filtered Repository instance.
 */
function myplugin_filter_traduttore_repository( \Required\Traduttore\Repository $repository = null, \Required\Traduttore\Project $project ) {
	$url  = $project->get_source_url_template();
	$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : null;
	
	if ( 'gitlab.example.com' === $host ) {
		return new MySelfhostedSourceForgeRepository( $project );
	}

	return $repository;
} 

add_filter( 'traduttore.repository', 'myplugin_filter_traduttore_repository', 10, 2 );
```

Ideally, you put this code into a custom WordPress plugin in your WordPress site that runs Traduttore.

[Learn more about developing WordPress plugins](https://developer.wordpress.org/plugins/).
