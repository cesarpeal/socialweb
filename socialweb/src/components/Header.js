import React, {useState, useEffect} from 'react';
import {Link, Outlet, useNavigate} from 'react-router-dom';
import Global from '../Global';
import Login from './Login';
import Register from './Register';
import axios from 'axios';

function Header(){
	const url = Global.url;
	let navigate = useNavigate();
	let token = localStorage.getItem("token");

	token = JSON.parse(token);
	useEffect(() =>{
		if(!user && token){
			getUser(token);
		}
	});

	const [search, setSearch] = useState("");
  	const [user, setUser] = useState("");

	const handleSearchChange = (e) =>{
		setSearch(e.target.value);
	}

	function submitSearch(e){
		e.preventDefault();
		navigate("search/"+search);
	}

	const getUser = (token) =>{
		var config = {
			method: 'post',
			url: url+'identity',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')}
		}
		axios(config)
			  .then(res => {
			  	setUser(res.data.user);
			  });
	}

	function logout(event){
		event.preventDefault();
		localStorage.removeItem("token");
		navigate("/");
	}

	return(
		<main>
			<div className="center">
				<header id="header">
					<h1 id="titulo"><Link replace to="/">SocialWeb</Link></h1>
					<div id="searcher">
						<form onSubmit={submitSearch}>
								<input type="text" name="search" value={search} onChange={handleSearchChange} />
								<input className="icon" type="submit" value="L" />
						</form>
					</div>
					<nav id="menu">
						<ul>
							<li><Link replace to="/">Home</Link></li>
							<li><Link replace to="/">Gente</Link></li>
							<li><Link replace to="/">Páginas</Link></li>
							<li><Link replace to="/">Ayuda</Link></li>
						</ul>
					</nav>
					<div id="login">
						{(!token) ? (
							<React.Fragment>
								<Login />
								<li><Link replace to="/register">Mi perfil</Link></li>
							</React.Fragment>
							) : (
							<React.Fragment>
								<h4>Bienvenido <span>{user.nombre} {user.apellidos}</span></h4>
								<nav className="dropdown">
									<ul>
										<li>Menú
											<ul>
												<li><Link replace to="/my-profile">Mi perfil</Link></li>
												<li><Link replace to="/edit-user">Editar cuenta</Link></li>
												<li><button onClick={logout}>Desconectarse</button></li>
											</ul>
										</li>
									</ul>
								</nav>
							</React.Fragment>
							)
						}
					</div>
				</header>
				<section id="content">
					<Outlet />
				</section>
			</div>
		</main>
	);
}
export default Header;
