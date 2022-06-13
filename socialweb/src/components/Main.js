import React, {Component, useState} from 'react';
import {Link} from 'react-router-dom';
import axios from 'axios';
import qs from 'qs';
import Global from '../Global';
import "react-responsive-carousel/lib/styles/carousel.min.css"; // requires a loader
import { Carousel } from 'react-responsive-carousel';
import like from '../assets/css/images/like.png';
import like2 from '../assets/css/images/like2.png';
import like3 from '../assets/css/images/like3.png';
import like4 from '../assets/css/images/like4.png';

class Main extends Component{
	url = Global.url;
	token = localStorage.getItem('token');
	
	commentRef = React.createRef();

	state = {
		posts: [],
		fotos_posts: [],
		busqueda: {users:[], pages:[]},
		limite: 10,
		like: {},
		user: {},
		userId: {},
		status: null
	}

	componentDidMount(){
		var search = this.props.search;
		var userId = this.props.userId;
		console.log(userId);

		if(this.token && (this.token !== null || this.token !== undefined)){
			this.getUser();
		}

		if(search && (search !== null || search !== undefined)){
			this.getBySearch(search);
		} else if(!isNaN(userId)){
			this.getIdUser(userId);
			this.homeUser(this.state.limite, userId);
		} else {
			this.home(this.state.limite);
		}
	}

	getUser = () => {
		var config = {
			method: 'post',
			url: this.url+'identity',
			headers: {'Content-Type':'x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')}
		}

		axios(config)
			.then(res => {
				this.setState({
					user: res.data.user
				});
			});
	}

	getIdUser = (idUser) => {
		this.setState({
			idUser: idUser
		});
	}

	getBySearch = (searched) => {
		axios.get(this.url+"search/"+searched)
			 .then(res => {
			 	this.setState({
			 		busqueda:{
				 		users: res.data.users,
				 		pages: res.data.pages
			 		},
				 	status: 'success'
			 	});
			 })
			 .catch(err => {
			 	this.setState({
				 	busqueda: [],
				 	status: 'success'
				 });
			 });
	}

	home = (limite) => {
		var data = qs.stringify({
			'json':'{"limite":'+limite+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'home',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			data: data
		};

		axios(config)
			 .then(res => {
			 	this.setState({
			 		posts: res.data.posts,
			 		fotos_posts: res.data.fotos_posts,
			 		status: 'success'
			 	});
			 });
	}

	homeUser = (limite, userId) => {
		var data = qs.stringify({
			'json':'{"limite":'+limite+', "userId":'+userId+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'user-posts',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		};

		axios(config)
			 .then(res => {
			 	this.setState({
			 		posts: res.data.posts,
			 		fotos_posts: res.data.fotos_posts,
			 		status: 'success'
			 	});
			 });
	}

	setHomeLimit = () => (e) =>{
		e.preventDefault();
		var limite = this.state.limite + 10;
		this.setState({
			limite: this.state.limite+10
		});
		this.home(limite);
	}

	like = (like_type, idPost) => (e) => {
		e.preventDefault();
		var data = qs.stringify({
			'json':'{"type":"'+like_type+'", "post":'+idPost+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'like',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		};

		axios(config)
			.then(res => {
				this.setState({
					like: res.data
				});
			});
	}

	deletePost = (idPost) => (e) => {
		e.preventDefault();
		var data = qs.stringify({
			'json':'{"idPost":'+idPost+'}'
		});

		var config = {
			method: 'delete',
			url: this.url+'borrar-post',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		};

		axios(config)
			.then(res => {
				this.setState({
					post: res.data.post
				});
			});
	}

	render(){
		if(this.state.posts.length >= 1 && this.state.status === 'success'){
		console.log(this.state.posts);
			var Posts = this.state.posts.map((post) => {
				return(
					<article className="post" key={post.id}>
							<div className="post-header">
								<div className="user">
									<img className="avatar" src={this.url+'foto/'+post.user.image} alt={post.user.image} />
									<h5>{post.user.nombre} {post.user.apellidos}</h5>
								</div>
								<h4>{post.titulo}</h4>
								{this.state.user.id === post.user.id &&
									<div className="post-manage">
										<Link replace to={"edit-post/"+post.id}>Editar post</Link>
										<button onClick={this.deletePost(post.id)}>Borrar post</button>
									</div>
								}
							</div>
							{this.state.fotos_posts.map((fotos_post) => {			
								return(
									<Carousel
									showArrows={true}
									infiniteLoop={true}
									showThumbs={false}
									>
									{fotos_post.map((foto_post) => {
										return(
											post.id === foto_post.post.id &&
												<img className="imagePost" src={this.url+'foto-post/'+foto_post.filename} alt={foto_post.filename} />
										);
									})}
									</Carousel>
								);								
							})}
							<div className="post-contenido">
								<p>{post.contenido}</p>
							</div>
						<Comment post={post.id}/>
						<Comments post={post.id} />
						<div className="dropdown-like">
							<ul>
								<li><button className="like-button" onClick={this.like("like", post.id)}><img className="like-icon" src={like} alt="like" /></button>
									<ul>
										<li><button className="like-button" onClick={this.like("love", post.id)}><img className="like-icon" src={like2} alt="love" /></button></li>
										<li><button className="like-button" onClick={this.like("fun", post.id)}><img className="like-icon" src={like3} alt="fun" /></button></li>
										<li><button className="like-button" onClick={this.like("dislike", post.id)}><img className="like-icon" src={like4} alt="dislike" /></button></li>
									</ul>
								</li>
							</ul>
						</div>
					</article>
				);
			});

			return(
				<div className="center">
					{Posts}
					<button onClick={this.setHomeLimit()}>Cargar más</button>
				</div>
			);
		} else if((this.state.busqueda.users.length >= 1 || this.state.busqueda.pages.length >= 1) && this.state.status === 'success'){
			var Users = this.state.busqueda.users.map((user) => {
				return(
					<h3>{user.nombre} {user.apellidos}</h3>
				);
			});

			var Pages = this.state.busqueda.pages.map((page) => {
				return(
					<h3>{page.titulo}</h3>
				);
			});

			return(
				<React.Fragment>
					<h2>Usuarios</h2>
					{Users}

					<h2>Páginas</h2>
					{Pages}
				</React.Fragment>
			);
		} else {
			return(
				<h2>Cargando...</h2>
			);
		}
	}
}

function Comment(props){
	const url = Global.url;
	const [comment, setComment] = useState("");

	const handleCommentChange = (e) => {
		setComment(e.target.value);
	}

	function handleComment(e){
		e.preventDefault();

		let data = qs.stringify({
			'json':'{"contenido":"'+comment+'"}'
		});

		let config = {
			method: 'post',
			url: url+'comentar-post/'+props.post,
			headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')},
			data: data
		}

		axios(config)
			.then(res => {
				setComment(comment);
			});
	}
	return(
		<form className="comment" onSubmit={handleComment}>
			<textarea name="comentario" cols="50" rows="2" value={comment} onChange={handleCommentChange}></textarea>
			<input type="submit" value="Comentar" />
			<div className="clearfix"></div>
		</form>
	);
}

class Comments extends Component{
	url = Global.url;
	state = {
		comentarios: [],
		status: null
	}

	componentDidMount(){
		var post = this.props.post;
		this.PostComments(post);
	}

	PostComments(idPost){
		axios.get(this.url+'post-comments/'+idPost)
			 .then(res => {
			 	this.setState({
			 		comentarios: res.data.comments,
			 		status: 'success'
			 	});
			 });
	}

	render(){
		if(this.state.comentarios.length >= 1 && this.state.status === 'success'){
			return(
				<div className="post-comments">
					{this.state.comentarios.map((comment) => {
						return(
							<div className="single-comment">
								<div className="user">
									<img className="avatar" src={this.url+'foto/'+comment.user.image} alt={comment.user.image} />
									<h5>{comment.user.nombre} {comment.user.apellidos}</h5>
								</div>
								<p>{comment.contenido}</p>
							</div>
						);
					})}
				</div>
			);
		}
	}
}

export default Main;