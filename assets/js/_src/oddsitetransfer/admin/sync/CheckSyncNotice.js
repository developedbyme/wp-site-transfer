"use strict";

import React from "react";
import ReactDOM from "react-dom";

// import CheckSyncNotice from "oddsitetransfer/admin/sync/CheckSyncNotice";
export default class CheckSyncNotice extends React.Component {

	constructor(props) {
		super(props);
		
		this.state = {
			"status": 0
		};
		
	}
	
	componentDidMount() {
		jQuery.get(this.props.transferUrl, (function(aData) {
			console.log(aData);
			if(aData.code === "success") {
				this.setState({"status": 1});
			}
			else {
				this.setState({"status": -1});
			}
		}).bind(this)).fail((function() {
			this.setState({"status": -1});
		}).bind(this));
	}
	
	render() {
		
		if(this.state.status === 1) {
			return <p>
				Post has been updated on all sites.
			</p>;
		}
		if(this.state.status === -1) {
			return <p>
				An error occured while transferring.
			</p>;
		}
		return <p>
			<span className="spinner is-active" style={{"float": "none"}}></span> Transferring post to all connected sites.
		</p>;
		
	}
}