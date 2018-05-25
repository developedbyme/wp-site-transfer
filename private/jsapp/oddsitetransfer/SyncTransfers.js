import React from "react";

import WprrBaseObject from "wprr/WprrBaseObject";

import JsonLoader from "wprr/utils/loading/JsonLoader";

//import SyncTransfers from "oddsitetransfer/SyncTransfers";
export default class SyncTransfers extends WprrBaseObject {
	constructor(props) {
		super(props);
		
		this.state["numberOfLoadedItems"] = 0;
		
		this._loaders = new Array();
	}
	
	_transferSynced(aLoader) {
		this.setState({"numberOfLoadedItems": this.state["numberOfLoadedItems"]+1})
	}
	
	componentWillMount() {
		
		let basePath = this.getReference("wprr/paths/rest");
		
		let currentArray = this.getSourcedProp("transfers");
		let currentArrayLength = currentArray.length;
		for(let i = 0; i < currentArrayLength; i++) {
			let currentTransfer = currentArray[i];
			
			let newLoader = new JsonLoader();
			
			newLoader.setUrl(basePath + "ost/v3/outgoing-transfer/" + currentTransfer.transferId);
			newLoader.onLoad = this._transferSynced.bind(this, newLoader);
			this._loaders.push(newLoader);
			
			newLoader.load(); //METODO: use loading sequence
		}
	}
	
	componentDidMount() {
		let transfers = this.getSourcedProp("transfers");
		if(transfers.length) {
			this.getReference("value/open").updateValue("open", true);
		}
	}
	
	_renderMainElement() {
		
		let numberOfTransfers = this.getSourcedProp("transfers").length;
		
		return <wrapper>
			Synced {this.state["numberOfLoadedItems"]} / {numberOfTransfers}
		</wrapper>
	}
}