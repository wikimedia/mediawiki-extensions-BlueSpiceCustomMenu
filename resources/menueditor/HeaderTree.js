ext = ext || {};
ext.custommenu = ext.custommenu || {};
ext.custommenu.menueditor = {};

ext.custommenu.menueditor.HeaderTree = function ( cfg ) {
	ext.custommenu.menueditor.HeaderTree.parent.call( this, cfg );
};

OO.inheritClass( ext.custommenu.menueditor.HeaderTree, ext.menueditor.ui.data.tree.MediawikiSidebarTree );

ext.custommenu.menueditor.HeaderTree.prototype.getPossibleNodesForLevel = function( lvl ) {
	switch ( lvl ) {
		case 0:
			return [ 'menu-raw-text' ];
		case 1:
			return [ 'menu-two-fold-link-spec' ];
		default:
			return [];
	}
};
