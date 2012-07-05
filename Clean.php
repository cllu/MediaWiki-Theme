<?php
/**
 * @ingroup Skins
 */
if( !defined( 'MEDIAWIKI' ) ) die( -1 );

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @ingroup Skins
 */
class SkinClean extends SkinTemplate {

	var $skinname = 'clean', $stylename = 'clean',
		$template = 'CleanTemplate', $useHeadElement = true;

	/** @var string The cache key under which to store the current page's TOC. */
	var $savedTocCacheKey;

	function setupSkinUserCss( OutputPage $out ) {
		global $wgHandheldStyle;

		parent::setupSkinUserCss( $out );

		$out->addStyle( 'clean/main.css',      'screen' );
	}

	/**
	 * Set the title, and create the cache key under which to store this page's TOC.
	 *
	 * @param Title $t The title to use.
	 *
	 * @see Skin::setTitle()
	 * @return void
	 */
	function setTitle( $t ) {
		parent::setTitle($t);
		$article_id = $this->mTitle->getArticleID();
		$this->savedTocCacheKey = wfMemcKey('clean', 'saved-toc', $article_id);
	}

	/**
	 * Completes the HTML for this page's TOC (note the closing UL tag) in a form
	 * suitable for use in the sidebar, and saves it to the cache for later use in
	 * {@link CleanTemplate::pageTocBox()}.
	 *
	 * @param string $toc HTML of the Table Of Contents for this page.
	 *
	 * @see Linker::tocList()
	 * @see CleanTemplate::pageTocBox()
	 * @return string An empty string; the TOC is pulled from cache, later.
	 */
	function tocList($toc) {
		global $parserMemc;
        $title = wfMsgHtml('toc');
		$savedToc = '<h3 class="widget-title">'.$title."</h2>".$toc."</ul>";
		$parserMemc->set($this->savedTocCacheKey, $savedToc);
		return '';
	}

}

/**
 * @todo document
 * @ingroup Skins
 */
class CleanTemplate extends QuickTemplate {
	var $skin;
	/**
	 * Template filter callback for Clean skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		global $wgRequest, $wgSitename, $wgCleanHeader, $wgStylePath;

		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );
		
		$this->set( 'sitename', $wgSitename );
		
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

		$this->html( 'headelement' );
?><div id="wrapper" class="hfeed">
	<div id="header" class="noprint">
				<div id="site-title">
					<a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" rel="home">
						<?php echo $this->text('sitename') ?>
					</a>
				</div>
	</div><!-- #header -->
      
      <div id="sidebar" class="widget-area noprint" role="complementary">
			<ul class="xoxo">
						
			<?php $sidebar = $this->data['sidebar'];
			foreach ($sidebar as $boxName => $cont) {
				if ( $boxName == 'SEARCH' ) {
					$this->searchBox();
				} elseif ( $boxName == 'LOGO' ) {
					$this->logo();
				} elseif ( $boxName == 'LANGUAGES' ) {
					$this->languageBox();
				} elseif ( $boxName == 'PAGETOC' ) {
					$this->pageTocBox();
				} elseif ($boxName!='MENUBAR' && $boxName!='TOOLBOX') {
					$this->customBox( $boxName, $cont );
				}
			} ?>
			
			</ul>
		</div><!-- #primary .widget-area -->

	<div id="main" <?php $this->html("specialpageattributes") ?>>
			<div id="content" role="main">			
				<div>
					<h2 class="entry-title"><?php $this->html('title') ?></h2>
					<div class="entry-meta">
						<?php $this->html('subtitle') ?>
					</div><!-- .entry-meta -->
					
					<div class="entry-content">
						<?php $this->html('bodytext') ?>
						<?php if($this->data['catlinks']) { $this->html('catlinks'); } ?>
						<?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
					</div>
				</div>
			
			</div><!-- #content -->
	</div><!-- #main -->

	<div id="footer" role="contentinfo" <?php $this->html('userlangattributes') ?>>
		<div id="colophon">
		    <div id="site-generator">Powered by
				<a href="http://mediawiki.org/" rel="generator">MediaWiki</a>
			</div>
		</div><!-- #colophon -->
        <ul id="content-actions">
            <?php
                foreach($this->data['content_actions'] as $key => $tab) {
                    echo '
                 <li id="' . Sanitizer::escapeId( "ca-$key" ) . '"';
                    if( $tab['class'] ) {
                        echo ' class="'.htmlspecialchars($tab['class']).'"';
                    }
                    echo '><a href="'.htmlspecialchars($tab['href']).'"';
                    # We don't want to give the watch tab an accesskey if the
                    # page is being edited, because that conflicts with the
                    # accesskey on the watch checkbox.  We also don't want to
                    # give the edit tab an accesskey, because that's fairly su-
                    # perfluous and conflicts with an accesskey (Ctrl-E) often
                    # used for editing in Safari.
                    if( in_array( $action, array( 'edit', 'submit' ) )
                    && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                        echo Linker::tooltip( "ca-$key" );
                    } else {
                        echo Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( "ca-$key" ) );
                    }
                    echo '>'.htmlspecialchars($tab['text']).'</a></li>';
                } ?>
        </ul>
	</div><!-- #footer -->

</div><!-- #wrapper -->

<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
</body>
</html>

	<?php
	wfRestoreWarnings();
	} // end of execute() method

	/*************************************************************************************************/
	function searchBox() {
		global $wgUseTwoButtonsSearchForm;
?>
	<li class="widget-container">
		<h3 class="widget-title"><label for="searchInput"><?php $this->msg('search') ?></label></h3>
			<form action="<?php $this->text('wgScript') ?>" id="searchform">
				<input type='hidden' name="title" value="<?php $this->text('searchtitle') ?>"/>
				<?php
		echo Html::input( 'search',
			isset( $this->data['search'] ) ? $this->data['search'] : '', 'search',
			array(
				'id' => 'searchInput',
				'title' => $this->skin->titleAttrib( 'search' ),
				'accesskey' => $this->skin->accesskey( 'search' )
			) ); ?>

				<input type='submit' name="go" class="searchButton" id="searchGoButton"
				value="<?php $this->msg('searcharticle') ?>"<?php echo Linker::tooltipAndAccesskeyAttribs( 'search-go' ); ?> />

			</form>
	</li>
<?php
	}

	function logo() {
		?>
		<li class="widget-container logo">
			<a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"<?php
			echo Linker::tooltipAndAccesskeyAttribs('p-logo') ?>>
				<img src="<?php $this->text('logopath') ?>" alt="Site Logo" />
			</a>
		</li>
		<?php
	}

	/*************************************************************************************************/
	function toolbox() {
		if($this->data['notspecialpage']) { ?>
				<li id="t-whatlinkshere"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
				?>"<?php echo Linker::tooltipAndAccesskeyAttribs('t-whatlinkshere') ?>><?php $this->msg('whatlinkshere') ?></a></li>
<?php
			if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
				<li id="t-recentchangeslinked"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
				?>"<?php echo Linker::tooltipAndAccesskeyAttribs('t-recentchangeslinked') ?>><?php $this->msg('recentchangeslinked-toolbox') ?></a></li>
<?php 		}
		}
		if( isset( $this->data['nav_urls']['trackbacklink'] ) && $this->data['nav_urls']['trackbacklink'] ) { ?>
			<li id="t-trackbacklink"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
				?>"<?php echo Linker::tooltipAndAccesskeyAttribs('t-trackbacklink') ?>><?php $this->msg('trackbacklink') ?></a></li>
<?php 	}
		if($this->data['feeds']) { ?>
			<li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
					?><a id="<?php echo Sanitizer::escapeId( "feed-$key" ) ?>" href="<?php
					echo htmlspecialchars($feed['href']) ?>" rel="alternate" type="application/<?php echo $key ?>+xml" class="feedlink"<?php echo Linker::tooltipAndAccesskeyAttribs('feed-'.$key) ?>><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;
					<?php } ?></li><?php
		}

		foreach( array('contributions', 'log', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {

			if($this->data['nav_urls'][$special]) {
				?><li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
				?>"<?php echo Linker::tooltipAndAccesskeyAttribs('t-'.$special) ?>><?php $this->msg($special) ?></a></li>
<?php		}
		}

		if(!empty($this->data['nav_urls']['print']['href'])) { ?>
				<li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
				?>" rel="alternate"<?php echo Linker::tooltipAndAccesskeyAttribs('t-print') ?>><?php $this->msg('printableversion') ?></a></li><?php
		}

		if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
				<li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
				?>"<?php echo Linker::tooltipAndAccesskeyAttribs('t-permalink') ?>><?php $this->msg('permalink') ?></a></li><?php
		} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
				<li id="t-ispermalink"<?php echo Linker::tooltip('t-ispermalink') ?>><?php $this->msg('permalink') ?></li><?php
		}

		wfRunHooks( 'CleanTemplateToolboxEnd', array( &$this ) );
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
	}

	/*************************************************************************************************/
	function languageBox() {
		if( $this->data['language_urls'] ) {
?>
	<li class="widget-container">
		<h3 class="widget-title" <?php $this->html('userlangattributes') ?>><?php $this->msg('otherlanguages') ?></h3>
			<ul>
<?php		foreach($this->data['language_urls'] as $langlink) { ?>
				<li class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
				?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
<?php		} ?>
			</ul>
	</li>
<?php
		}
	}

	/*************************************************************************************************/
	function pageTocBox() {
		global $parserMemc;
		echo '<li class="widget-container">';
		echo $parserMemc->get($this->skin->savedTocCacheKey);
		echo '</li>';
	}

	/*************************************************************************************************/
	function customBox( $bar, $cont ) { ?>

		<li class='widget-container' id='<?php echo Sanitizer::escapeId( "p-$bar" ) ?>'<?php echo Linker::tooltip('p-'.$bar) ?>>
		<h3 class="widget-title">
			<?php $out = wfMsg( $bar );
			if (wfEmptyMsg($bar, $out)) echo htmlspecialchars($bar);
			else echo htmlspecialchars($out); ?>
		</h3>
<?php   if ( is_array( $cont ) ) { ?>
			<ul>
<?php 			foreach($cont as $key => $val) { ?>
				<li id="<?php echo Sanitizer::escapeId($val['id']) ?>"<?php if ( $val['active'] ) { ?> class="active" <?php }
				?>><a href="<?php echo htmlspecialchars($val['href']) ?>"<?php echo Linker::tooltipAndAccesskeyAttribs($val['id']) ?>>
					<?php echo htmlspecialchars($val['text']) ?></a>
				</li>
<?php			} ?>
			</ul>
<?php   } else {
			# allow raw HTML block to be defined by extensions
			print $cont;
		}
		echo '</li>';

	} // customBox()
	
} // end of class


