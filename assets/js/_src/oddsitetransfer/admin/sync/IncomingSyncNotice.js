"use strict";

import React from "react";
import ReactDOM from "react-dom";

// import IncomingSyncNotice from "oddsitetransfer/admin/sync/IncomingSyncNotice";
export default class IncomingSyncNotice extends React.Component {

	constructor(props) {
		super(props);
		
		this.state = {
			
		};
		
		
	}
	
	render() {
		
		return <p>
			<span className="do-not-update">This post is synced from another server. Any changes done will be overwritten at the next sync.</span>
			<span className="sync-date">Last sync: {this.props.syncDate}</span>
			<span className="sync-id">Sync id: {this.props.syncId}</span>
		</p>;
		
	}
}