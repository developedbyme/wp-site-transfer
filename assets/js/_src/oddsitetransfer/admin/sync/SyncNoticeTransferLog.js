"use strict";

import React from "react";
import ReactDOM from "react-dom";

// import SyncNoticeTransferLog from "oddsitetransfer/admin/sync/SyncNoticeTransferLog";
export default class SyncNoticeTransferLog extends React.Component {

	constructor(props) {
		super(props);
		
		this.state = {
			"showingLog": false
		};
		
		this._toggleLogsBound = this.toggleLogs.bind(this);
	}
	
	_createLogItem(aData, aKey) {
		//console.log("_createLogItem");
		//console.log(aData);
		
		return <div key={aKey} className={"log-item type-"+aData.type}>{aData.message}</div>
	}
	
	toggleLogs() {
		this.setState({"showingLog": !this.state.showingLog});
	}
	
	componentDidMount() {
		
	}
	
	render() {
		
		var logElement = null;
		var logButton = null;
		
		if(this.state.showingLog) {
			var logs = new Array();
			var currentArray = this.props.log;
			var currentArrayLength = currentArray.length;
			for(var i = 0; i < currentArrayLength; i++) {
				var newLog = this._createLogItem(currentArray[i], i);
				logs.push(newLog);
			}
			
			logButton = <div className="button toggle-button" onClick={this._toggleLogsBound}>Hide log</div>;
			logElement = <div className="log-items">
				{logs}
			</div>;
		}
		else {
			logButton = <div className="button toggle-button" onClick={this._toggleLogsBound}>Show log</div>;
		}
		
		var statusMessage = null;
		switch(this.props.status) {
			case "sent":
				if(this.props.code === 'sent-non-existing') {
					statusMessage = <span className="transfer-status">Ignored</span>;
				}
				else {
					statusMessage = <span className="transfer-status"><a href={this.props.url}>{this.props.url}</a></span>;
				}
				break;
			case "ignored":
				statusMessage = <span className="transfer-status">Ignored</span>;
				break;
			case "error":
				if(this.props.code === 'logged-error') {
					statusMessage = <span className="transfer-status">An error occured, check the log</span>;
				}
				else {
					statusMessage = <span className="transfer-status">An unknown error occured</span>;
				}
				break;
			default:
				statusMessage = <span className="transfer-status">Unknown status</span>;
				break;
		}
		
		return <div className={"transfer-log status-" + this.props.status + " code-" + this.props.code}>
			<div className="title">
				<span className="server-name">{this.props.name}:</span>
				{statusMessage}
				{logButton}
			</div>
			{logElement}
		</div>;
		
	}
}