import React from 'react';
import Editable from '../Editable';
import {jwt} from '../jwt';
import fetchFromApi from '../../helpers/fetchFromApi';
import { Link } from 'react-router-dom'

import './index.css';

/**
 * @prop profileLink
 * @prop author
 * @prop authorid
 * @prop content
 * @prop issue
 * @prop name
 * @prop addComment - function, parent component should add value returned to comment list when called
 * @prop deleteComment - function, parent component should delete comment with id returned
 */
class Comment extends React.Component {

    constructor(props) {
        super(props);

        this.submit = this.submit.bind(this);
        this.delete = this.delete.bind(this);

        this.state = {
            content: this.props.content,
            author: this.props.author,
            authorid: this.props.authorid,
            profileLink: this.props.profileLink,
            id: this.props.id
        }
    }

    submit() {

        const info = {
            issue: this.props.issue,
            url: this.props.name,
            content: this.state.content
        }

        fetchFromApi("comment", "post", info);

        this.props.addComment({
            author_name: "You",
            authorid: jwt.id,
            content: this.state.content,
            created: Date.now(),
            id: Date.now(), // random thing to be used as comment key
            profile_link: jwt.email
        });
    }

    delete() {

        const info = {
            id: this.props.id
        }

        fetchFromApi("comment", "delete", info);

        this.props.deleteComment(this.props.id);
    }

    newComment() {

        return <article id="reply" className="comment">
                    <Editable
                        canEdit={!!jwt.id /*if logged in*/}
                        buttons="basic"
                        submit={this.submit}
                        children={
                            <div
                              onBlur={e => this.setState({content: e.target.innerHTML})}
                              className="content"
                              dangerouslySetInnerHTML={{__html: this.props.content}}
                            />
                        }
                    />
                </article>
    }

    oldComment() {

        return <article className="comment">
                <Link className="author" to={'/u/'+this.props.profileLink}>{this.props.author}</Link>
                <div className="content" dangerouslySetInnerHTML={{__html: this.props.content}} />
                {this.props.content !== "deleted" &&  (jwt.level > 2 || jwt.id === this.props.authorid) ?
                    <button className="deleteReply" onClick={this.delete}>Delete</button> :
                    ''
                }
               </article>
    }

    switchboard() {


        return !this.props.author ? this.newComment() : this.oldComment();
    }

    render() {

        return (
            this.switchboard()
        );
    }
}

export default Comment;