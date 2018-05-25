import React from "react";
import Wprr from "wprr";

import ImageLoaderManager from "wprr/imageloader/ImageLoaderManager";

import AdminModuleCreator from "wprr/modulecreators/AdminModuleCreator";

import GlobalSyncModule from "oddsitetransfer/GlobalSyncModule";

console.log("Odd site transfer admin.js");

{
	let globalWprr = new Wprr();
	globalWprr.addGlobalReference(window);
	
	if(!globalWprr.imageLoaderManager) globalWprr.imageLoaderManager = new ImageLoaderManager();
	
	globalWprr.addModuleCreator("globalSync", AdminModuleCreator.create(GlobalSyncModule));
	
	globalWprr.imageLoaderManager.start();
}