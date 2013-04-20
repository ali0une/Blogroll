<?php if(!defined('PLX_ROOT')) exit; ?>

<h2>Help</h2>
<p>Blogroll plugin help file</p>

<p>&nbsp;</p>
<h3>Installation</h3>
<p>Activate plugin.<br/>
Edit the template file "sidebar.php". Add following code where you want to see your links:</p>
<pre>
	&lt;h3&gt;&lt;?php eval($plxShow-&gt;callHook(&#039;showBlogrollHead&#039;)); ?&gt;&lt;/h3&gt;
		&lt;ul&gt;
			&lt;?php eval($plxShow-&gt;callHook(&#039;showBlogroll&#039;)); ?&gt;
		&lt;/ul&gt;
</pre>
<p>&nbsp;</p>
<p>If you want to specify format:</p>
<pre>
	&lt;h3&gt;&lt;?php eval($plxShow-&gt;callHook(&#039;showBlogrollHead&#039;)); ?&gt;&lt;/h3&gt;
		&lt;ul&gt;
&lt;?php eval($plxShow-&gt;callHook('showBlogroll', '&lt;li style="background:url(\'#icon\') no-repeat scroll 0 0 transparent;padding-left:20px;background-size:16px 16px;"&gt;
&lt;a target="_blank" href="#url" hreflang="#langue" title="#description"&gt;#title&lt;/a&gt;
&lt;/li&gt;')); ?&gt;
		&lt;/ul&gt;
</pre>


<p>&nbsp;</p>
<h3>Usage</h3>
<p>
This plugin adds an entry "Blogroll" on the left side, of the site administration to manage your links.
</p>

<p>&nbsp;</p>
<h3>Configuration</h3>
<p>
in the configuration part of the plugin (Parameters > plugins > Blogroll configuration), you can change the configuration xml file location and the title that appears in the sidebar of the public part.
</p>
