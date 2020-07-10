import { Component } from 'react';

export default (props) => {
    return (
        <p>
            If you do not want to be building front end viewing experience with React,
            then you should change your entry point in webpack.config.js and obviously have
            other plans already in mind.

            Otherwise, build components within the src/components folder, and import them into
            index.js to build your project.
        </p>
    );
}