import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, IndexRedirect, useRouterHistory } from 'react-router';
import { createHashHistory } from 'history';
import PropTypes from 'prop-types';

import KnowledgeBase from 'help/knowledge_base.jsx';
import SystemInfo from 'help/system_info.jsx';
import SystemStatus from 'help/system_status.jsx';
import YourPrivacy from 'help/your_privacy.jsx';

const history = useRouterHistory(createHashHistory)({ queryKey: false });

class App extends React.Component {
  render() {
    return this.props.children;
  }
}

App.propTypes = {
  children: PropTypes.element.isRequired,
};

const container = document.getElementById('help_container');

if (container) {
  ReactDOM.render((
    <Router history={history}>
      <Route path="/" component={App}>
        <IndexRedirect to="knowledgeBase" />
        {/* Pages */}
        <Route path="knowledgeBase(/)**" params={{ tab: 'knowledgeBase' }} component={KnowledgeBase} />
        <Route path="systemStatus(/)**" params={{ tab: 'systemStatus' }} component={SystemStatus} />
        <Route path="systemInfo(/)**" params={{ tab: 'systemInfo' }} component={SystemInfo} />
        <Route path="yourPrivacy(/)**" params={{ tab: 'yourPrivacy' }} component={YourPrivacy} />
      </Route>
    </Router>
  ), container);
}
