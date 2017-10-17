package handler

import (
	"crypto/rsa"
	"io/ioutil"
	"os"
	"strconv"

	"hermes/middlewares"
	"hermes/ratings/parser"
	"hermes/responses"

	"github.com/dgrijalva/jwt-go"
	"github.com/go-playground/validator"
	"github.com/labstack/echo"
	"github.com/labstack/echo/middleware"
	"github.com/labstack/gommon/log"
)

type RequestValidator struct {
	validator *validator.Validate
}

func (rv *RequestValidator) Validate(request interface{}) error {
	return rv.validator.Struct(request)
}

func Handler(port int, routes map[string]echo.HandlerFunc) *echo.Echo {
	e := echo.New()
	validate := validator.New()
	env := os.Getenv("HERMES_RATINGS_ENV")
	key := getPublicKey(e)

	jwtConfig := middleware.JWTConfig{
		SigningKey:    key,
		SigningMethod: "RS256",
		ContextKey:    "jwt"}

	parser.RegisterCustomValidators(validate)

	if env == "DEV" {
		e.Logger.SetLevel(log.DEBUG)

		e.Debug = true
	} else {
		e.Logger.SetLevel(log.ERROR)
		e.Pre(middleware.HTTPSRedirect())
		e.Use(middleware.JWTWithConfig(jwtConfig))
	}

	e.Use(middleware.Secure())
	e.Use(middleware.Logger())
	e.Use(middleware.Recover())
	e.Use(middleware.BodyLimit("20K"))
	e.Use(middlewares.NotImplementedMiddleware)
	e.Use(middlewares.NotAcceptableMiddleware)
	e.Use(middlewares.BadRequestMiddleware)
	e.Use(middlewares.UnsupportedMediaTypeMiddleware)
	e.Use(middlewares.CorsMiddleware)

	e.OPTIONS("/", routes["OptionsRoot"])
	e.OPTIONS("/ratings", routes["OptionsRatings"])
	e.POST("/ratings", routes["PostRatings"])

	e.HTTPErrorHandler = responses.ErrorHandler
	e.Validator = &RequestValidator{validator: validate}
	e.Server.Addr = ":" + strconv.Itoa(port)

	return e
}

func getPublicKey(echo *echo.Echo) *rsa.PublicKey { // TODO: Move to a shared utils package
	keyData, readErr := ioutil.ReadFile(os.Getenv("HERMES_RATINGS_PUBLICKEY"))

	if readErr != nil {
		echo.Logger.Fatal("Could not find key file")
	}

	key, parseErr := jwt.ParseRSAPublicKeyFromPEM(keyData)

	if parseErr != nil {
		echo.Logger.Fatal(parseErr.Error())
	}

	/*
		token := jwt.New(jwt.SigningMethodRS256)
		privKey, _ := ioutil.ReadFile(os.Getenv("HERMES_RATINGS_PRIVATEKEY"))
		privKeyParsed, _ := jwt.ParseRSAPrivateKeyFromPEM(privKey)

		t, _ := token.SignedString(privKeyParsed)

		echo.Logger.Fatal(t)
	*/

	return key
}
