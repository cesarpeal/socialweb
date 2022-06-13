import React, {useEffect, useState} from 'react';
import {useNavigate, useParams} from 'react-router-dom';
import axios from 'axios';
import qs from 'qs';
import Global from '../Global';

function Register() {
	let navigate = useNavigate();
	const url = Global.url;
	let token = localStorage.getItem('token');
	const [user, setUser] = useState({
		nombre: '',
		apellidos: '',
		telefono: '',
		direccion: '',
		email: '',
		password: '',
	});
	const [status, setStatus] = useState('');

	useEffect(() => {
		console.log(status);
		if(token && !status){
			getUser();
		}
	});

	function getUser(){
		let config = {
			method: 'post',
			url: url+'identity',
			headers: {'Content-Type':'x-www-form-urlencoded', 'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')}
		}

		axios(config)
		.then(res => {
			setUser(res.data.user);
			setStatus(res.data.status);
		});
	}

	const handleChange = (e) => {    
	  setUser({
	    ...user,                                
	    [e.target.name]: e.target.value,     
	  });
	};

	function handleSubmit(e){
		e.preventDefault();
		let data = qs.stringify({
			'json':'{"nombre":"'+user.nombre+'", "apellidos":"'+user.apellidos+'", "telefono":"'+user.telefono+'", "direccion":"'+user.direccion+'", "email":"'+user.email+'", "password":"'+user.password+'"}'
		});

		let config = {
			method: 'post',
			url: url+'registro',
			headers: {'Content-Type':'application/x-www-form-urlencoded'},
			data: data
		}

		axios(config)
			.then(
				navigate("/")
			);
	}

	return(
		<form className="form-user" onSubmit={handleSubmit}>
			<div className="form-group">
				<label htmlFor="nombre">Nombre: </label>
				<input type="text" name="nombre" value={user.nombre} onChange={handleChange} />
			</div>

			<div className="form-group">
				<label htmlFor="apellidos">Apellidos: </label>
				<input type="text" name="apellidos" value={user.apellidos} onChange={handleChange} />
			</div>
			
			<div className="form-group">
				<label htmlFor="direccion">Direccion: </label>
				<input type="text" name="direccion" value={user.direccion} onChange={handleChange} />
			</div>

			<div className="form-group">
				<label htmlFor="telefono">Telefono: </label>
				<input type="text" name="telefono" value={user.telefono} onChange={handleChange} />
			</div>

			{!localStorage.getItem('token') &&
			<React.Fragment>
				<div className="form-group">
					<label htmlFor="email">Email: </label>
					<input type="text" name="email" value={user.email} onChange={handleChange} />
				</div>

				<div className="form-group">
					<label htmlFor="password">Password: </label>
					<input type="text" name="password" value={user.password} onChange={handleChange} />
				</div>
			</React.Fragment>
			}

			<div className="form-group">
				<input type="submit" value="enviar" />
			</div>
		</form>
	);
}
export default Register;