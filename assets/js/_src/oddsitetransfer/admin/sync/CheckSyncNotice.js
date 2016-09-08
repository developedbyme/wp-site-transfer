"use strict";

import React from "react";
import ReactDOM from "react-dom";

import SyncNoticeTransferLog from "oddsitetransfer/admin/sync/SyncNoticeTransferLog";

// import CheckSyncNotice from "oddsitetransfer/admin/sync/CheckSyncNotice";
export default class CheckSyncNotice extends React.Component {

	constructor(props) {
		super(props);
		
		this.state = {
			"status": 0,
			"transferStatus": "none",
			"transfers": []
		};
		
		this.resyncBound = this.resync.bind(this);
	}
	
	_createTransfer(aData) {
		//console.log("_createTransfer");
		//console.log(aData);
		
		var log = [];
		if(aData.result && aData.result.log) {
			log = aData.result.log;
		}
		
		
		return <SyncNoticeTransferLog key={aData.name} name={aData.name} status={aData.status} code={aData.code} url={aData.url} log={log} />;
	}
	
	_setResultData(aData) {
		if(aData.code === "success") {
			
			var transfers = new Array();
			
			var currentArray = aData.data.transfer;
			var currentArrayLength = currentArray.length;
			for(var i = 0; i < currentArrayLength; i++) {
				transfers.push(this._createTransfer(currentArray[i]));
			}
			
			this.setState({"status": 1, "transferStatus": aData.data.status, "transfers": transfers});
		}
		else {
			this.setState({"status": -1});
		}
	}
	
	componentDidMount() {
		jQuery.get(this.props.transferUrl, (function(aData) {
			console.log(aData);
			this._setResultData(aData);
		}).bind(this)).fail((function() {
			this.setState({"status": -1});
		}).bind(this));
	}
	
	resync() {
		this.setState({"status": 0, "transfers": []});
		jQuery.get(this.props.transferUrl + "?force=1&forceDependencies=5", (function(aData) {
			console.log(aData);
			this._setResultData(aData);
		}).bind(this)).fail((function() {
			this.setState({"status": -1});
		}).bind(this));
	}
	
	render() {
		
		if(this.state.status === 1) {
			if(this.state.transferStatus === 'sent') {
				return <div>
					<p>Post has been updated on all sites. <span className="resync" onClick={this.resyncBound}>(Resync)</span></p>
					<div>
						{this.state.transfers}
					</div>
				</div>;
			}
			else {
				return <div>
					<p>Error occured while transferring. <span className="resync" onClick={this.resyncBound}>(Resync)</span></p>
					<div>
						{this.state.transfers}
					</div>
				</div>;
			}
		}
		if(this.state.status === -1) {
			return <p>
				An error occured while transferring. <span className="resync" onClick={this.resyncBound}>(Resync)</span>
			</p>;
		}
		return <p>
			<span className="spinner is-active" style={{"float": "none"}}></span> Transferring post to all connected sites.
		</p>;
		
	}
}