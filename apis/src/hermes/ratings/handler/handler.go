package handler

import (
	"os"
	"strconv"

	"hermes/middlewares"
	"hermes/ratings/parser"
	"hermes/responses"
	"hermes/utils"

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

	parser.RegisterCustomValidators(validate)

	if os.Getenv("HERMES_RATINGS_ENV") == "DEV" {
		e.Debug = true

		e.Logger.SetLevel(log.DEBUG)
	} else {
		jwtConfig := middleware.JWTConfig{
			SigningKey:    utils.GetPublicKey("HERMES_RATINGS_PUBLICKEY", e),
			SigningMethod: "RS256",
			ContextKey:    "jwt"}

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
