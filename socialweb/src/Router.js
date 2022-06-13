import React, {Component} from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';

import Header from './components/Header';
import Main from './components/Main';
import MyProfile from './components/MyProfile';
import UserProfile from './components/UserProfile';
import Search from './components/Search';
import CreatePost from './components/CreatePost';
import EditPost from './components/EditPost';
import Register from './components/Register';

class Router extends Component{

	render(){
		return(
			<BrowserRouter>
					<Routes>
						<Route path="/" element={<Header />}>
							<Route path="/" element={<Main />} />
							<Route path="/my-profile" element={<MyProfile />} />
							<Route path="/user-profile/:idUser" element={<UserProfile />} />
							<Route path="/register" element={<Register />} />
							<Route path="/edit-user" element={<Register />} />
							<Route path="/search/:search" element={<Search />} />
							<Route path="/create-post" element={<CreatePost />} />
							<Route path="/edit-post/:idPost" element={<EditPost />} />
							<Route path="*" element={<div>404 Not found</div>} />
						</Route>
					</Routes>
			</BrowserRouter>
		);
	}
}

export default Router;